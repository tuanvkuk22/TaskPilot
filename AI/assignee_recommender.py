"""Train and serve a deep-learning assignee recommender from TaskPilot CSV data or live input."""

from __future__ import annotations

import argparse
import ast
import json
import os
import random
import re
import sys
from collections import Counter, defaultdict
from pathlib import Path
from typing import Iterable

import numpy as np
import pandas as pd
import torch
from torch import nn
from torch.utils.data import DataLoader, Dataset

TOKEN_PATTERN = re.compile(r"[a-z0-9]+")
DEFAULT_DATASET = Path(__file__).resolve().parent / "dataset"
DEFAULT_CHECKPOINT = Path(__file__).resolve().parent / "models" / "assignee_recommender.pt"


class IssueDataset(Dataset):
    """PyTorch Dataset for TaskPilot issue data."""

    def __init__(self, tokens: np.ndarray, categories: np.ndarray, numeric: np.ndarray, labels: np.ndarray):
        self.tokens = torch.as_tensor(tokens, dtype=torch.long)
        self.categories = torch.as_tensor(categories, dtype=torch.long)
        self.numeric = torch.as_tensor(numeric, dtype=torch.float32)
        self.labels = torch.as_tensor(labels, dtype=torch.long)

    def __len__(self) -> int:
        return len(self.labels)

    def __getitem__(self, index: int):
        return (
            self.tokens[index],
            self.categories[index],
            self.numeric[index],
            self.labels[index],
        )


class AssigneeNetwork(nn.Module):
    """Enhanced Deep Learning Multi-Branch Neural Network for Assignee Recommendation."""

    def __init__(
        self,
        vocabulary_size: int,
        category_sizes: list[int],
        numeric_size: int,
        class_count: int,
        embedding_dim: int = 128,
        cat_embed_dim: int = 32,
        hidden_dim1: int = 512,
        hidden_dim2: int = 256,
    ):
        super().__init__()
        self.token_embedding = nn.Embedding(vocabulary_size, embedding_dim, padding_idx=0)
        self.text_conv = nn.Conv1d(embedding_dim, embedding_dim, kernel_size=3, padding=1)
        
        self.category_embeddings = nn.ModuleList(
            [nn.Embedding(max(size, 2), cat_embed_dim) for size in category_sizes]
        )
        input_size = embedding_dim + len(category_sizes) * cat_embed_dim + numeric_size

        self.fc1 = nn.Linear(input_size, hidden_dim1)
        self.norm1 = nn.LayerNorm(hidden_dim1)
        self.act1 = nn.GELU()
        self.dropout1 = nn.Dropout(0.2)

        self.fc2 = nn.Linear(hidden_dim1, hidden_dim2)
        self.norm2 = nn.LayerNorm(hidden_dim2)
        self.act2 = nn.GELU()
        self.dropout2 = nn.Dropout(0.2)

        self.fc3 = nn.Linear(hidden_dim2, hidden_dim2)
        self.norm3 = nn.LayerNorm(hidden_dim2)
        self.act3 = nn.GELU()

        self.output_layer = nn.Linear(hidden_dim2, class_count)

    def forward(self, tokens: torch.Tensor, categories: torch.Tensor, numeric: torch.Tensor) -> torch.Tensor:
        # Text embedding branch: Token Embedding + Conv1d + Masked Pooling
        mask = tokens.ne(0).unsqueeze(-1)
        token_vectors = self.token_embedding(tokens) # [B, T, D]
        
        # Apply 1D Convolution over sequence (transpose to [B, D, T])
        conv_out = self.text_conv(token_vectors.transpose(1, 2)).transpose(1, 2)
        token_vectors = token_vectors + conv_out # Residual text feature
        
        token_sum = (token_vectors * mask).sum(dim=1)
        token_count = mask.sum(dim=1).clamp_min(1)
        text_vector = token_sum / token_count

        # Categorical embedding branch
        category_vectors = [
            embedding(categories[:, index])
            for index, embedding in enumerate(self.category_embeddings)
        ]

        # Fusion & Deep Multi-layer Perceptron (MLP)
        combined = torch.cat([text_vector, *category_vectors, numeric], dim=1)
        x = self.dropout1(self.act1(self.norm1(self.fc1(combined))))
        res = self.dropout2(self.act2(self.norm2(self.fc2(x))))
        x = self.act3(self.norm3(self.fc3(res))) + res # Residual block
        return self.output_layer(x)


def normalize(value) -> str:
    if value is None:
        return ""
    value = str(value).strip()
    return "" if value.lower() in {"nan", "none", "null"} else value


def tokenize(value: str) -> list[str]:
    return TOKEN_PATTERN.findall(normalize(value).lower())


def combined_text(row: dict) -> str:
    fields = (
        row.get("projectname"),
        row.get("project_description"),
        row.get("project_category"),
        row.get("project_lead"),
        row.get("project_components"),
        row.get("project_issue_types"),
        row.get("summary"),
        row.get("description"),
        row.get("issuetype"),
        row.get("priority"),
        row.get("components"),
        row.get("labels"),
        row.get("reporter"),
    )
    return " ".join(normalize(field).replace("|", " ").replace("/", " ") for field in fields)


def load_project_context(dataset_dir: Path, chunksize: int) -> dict[str, dict]:
    project_path = dataset_dir / "project_clean.csv"
    if not project_path.exists():
        return {}
    columns = ["key", "name", "description", "projectCategory", "lead", "components", "issueTypes"]
    context = {}
    for row in row_dicts(project_path, columns, chunksize):
        values = {
            "project_description": row.get("description"),
            "project_category": row.get("projectCategory"),
            "project_lead": row.get("lead"),
            "project_components": row.get("components"),
            "project_issue_types": row.get("issueTypes"),
        }
        for identifier in (row.get("key"), row.get("name")):
            identifier = normalize(identifier)
            if identifier:
                context[identifier] = values
    return context


def enrich_project(row: dict, project_context: dict[str, dict]) -> dict:
    project = project_context.get(normalize(row.get("projectname")), {})
    if project:
        row = {**row, **project}
    return row


def csv_chunks(path: Path, columns: list[str], chunksize: int = 50_000) -> Iterable[pd.DataFrame]:
    available = pd.read_csv(path, nrows=0).columns.tolist()
    usecols = [column for column in columns if column in available]
    yield from pd.read_csv(
        path,
        usecols=usecols,
        dtype=str,
        keep_default_na=False,
        chunksize=chunksize,
        low_memory=False,
    )


def row_dicts(path: Path, columns: list[str], chunksize: int = 50_000):
    for chunk in csv_chunks(path, columns, chunksize):
        names = chunk.columns.tolist()
        for values in chunk.itertuples(index=False, name=None):
            yield dict(zip(names, values))


def aggregate_history(dataset_dir: Path, chunksize: int) -> dict[str, tuple[float, float, float, float]]:
    history = defaultdict(lambda: [0.0, 0.0, 0.0, 0.0])
    events_path = dataset_dir / "events.csv"
    if events_path.exists():
        columns = ["issue", "field", "author"]
        for chunk in csv_chunks(events_path, columns, chunksize):
            issue = chunk["issue"].map(normalize)
            valid = issue.ne("")
            grouped = pd.DataFrame({"issue": issue[valid], "field": chunk["field"][valid].str.lower()})
            counts = grouped.groupby("issue").size()
            status_counts = grouped[grouped["field"].eq("status")].groupby("issue").size()
            assignee_counts = grouped[grouped["field"].eq("assignee")].groupby("issue").size()
            for key, count in counts.items():
                history[key][0] += float(count)
            for key, count in status_counts.items():
                history[key][1] += float(count)
            for key, count in assignee_counts.items():
                history[key][2] += float(count)

    worklogs_path = dataset_dir / "worklogs.csv"
    if worklogs_path.exists():
        columns = ["issue", "timeSpentSeconds", "author"]
        for chunk in csv_chunks(worklogs_path, columns, chunksize):
            issue = chunk["issue"].map(normalize)
            seconds = pd.to_numeric(chunk["timeSpentSeconds"], errors="coerce").fillna(0.0)
            grouped = pd.DataFrame({"issue": issue, "seconds": seconds})
            grouped = grouped[grouped["issue"].ne("")]
            aggregates = grouped.groupby("issue")["seconds"].agg(["count", "sum"])
            for key, values in aggregates.iterrows():
                history[key][3] += float(values["sum"])
                history[key][0] += float(values["count"])
    return {key: tuple(value) for key, value in history.items()}


def collect_metadata(
    issue_path: Path, project_context: dict[str, dict], max_samples: int, min_label_count: int, chunksize: int
):
    token_counts = Counter()
    category_counts = [Counter() for _ in range(5)]
    label_counts = Counter()
    accepted = 0
    columns = [
        "_id",
        "projectname",
        "summary",
        "description",
        "issuetype",
        "priority",
        "status",
        "assignee",
        "reporter",
        "components",
        "labels",
    ]
    for row in row_dicts(issue_path, columns, chunksize):
        row = enrich_project(row, project_context)
        label = normalize(row.get("assignee"))
        if not label or accepted >= max_samples:
            continue
        accepted += 1
        label_counts[label] += 1
        values = [
            row.get("projectname"),
            row.get("issuetype"),
            row.get("priority"),
            row.get("status"),
            row.get("reporter"),
        ]
        for counter, value in zip(category_counts, values):
            counter[normalize(value)] += 1
        token_counts.update(tokenize(combined_text(row)))

    labels = [label for label, count in label_counts.most_common() if count >= min_label_count]
    vocabulary = {"<pad>": 0, "<unk>": 1}
    vocabulary.update({token: index for index, (token, _) in enumerate(token_counts.most_common(50_000), start=2)})
    categories = []
    for counter in category_counts:
        mapping = {"": 0, "<unk>": 1}
        valid_items = [val for val, _ in counter.most_common(20_000) if normalize(val)]
        mapping.update({val: idx for idx, val in enumerate(valid_items, start=2)})
        categories.append(mapping)
    return vocabulary, categories, labels


def encode_category(value, mapping: dict) -> int:
    return mapping.get(normalize(value), 1)


def encode_tokens(text: str, vocabulary: dict[str, int], max_tokens: int) -> list[int]:
    ids = [vocabulary.get(token, 1) for token in tokenize(text)[:max_tokens]]
    return ids + [0] * (max_tokens - len(ids))


def build_examples(
    issue_path: Path, history, project_context, vocabulary, categories, labels, max_samples, max_tokens, chunksize
):
    label_to_id = {label: index for index, label in enumerate(labels)}
    token_rows, category_rows, numeric_rows, target_rows = [], [], [], []
    columns = [
        "_id",
        "projectname",
        "summary",
        "description",
        "issuetype",
        "priority",
        "status",
        "assignee",
        "reporter",
        "components",
        "labels",
    ]
    for row in row_dicts(issue_path, columns, chunksize):
        row = enrich_project(row, project_context)
        if len(target_rows) >= max_samples:
            break
        label = normalize(row.get("assignee"))
        if label not in label_to_id:
            continue
        issue_history = history.get(normalize(row.get("_id")), (0.0, 0.0, 0.0, 0.0))
        token_rows.append(encode_tokens(combined_text(row), vocabulary, max_tokens))
        category_rows.append([
            encode_category(row.get("projectname"), categories[0]),
            encode_category(row.get("issuetype"), categories[1]),
            encode_category(row.get("priority"), categories[2]),
            encode_category(row.get("status"), categories[3]),
            encode_category(row.get("reporter"), categories[4]),
        ])
        numeric_rows.append([np.log1p(value) for value in issue_history])
        target_rows.append(label_to_id[label])

    numeric = np.asarray(numeric_rows, dtype=np.float32)
    if len(numeric):
        numeric = (numeric - numeric.mean(axis=0)) / (numeric.std(axis=0) + 1e-6)
    return np.asarray(token_rows), np.asarray(category_rows), numeric, np.asarray(target_rows), label_to_id


def reciprocal_rank_sum(ranking: torch.Tensor, targets: torch.Tensor) -> float:
    """Sum of 1/(rank + 1) for the true class of each row.

    `ranking` is a [batch, classes] tensor of class indices sorted by descending
    score. `nonzero(as_tuple=False)` on the 2D equality mask always returns
    shape [num_matches, 2]: column 0 is the batch row, column 1 is the 0-based
    rank. Index that rank column directly — do not branch on `ndim`, which is
    0 after unpacking a row and 1 if the row is left packed.
    """
    matches = ranking.eq(targets.unsqueeze(1)).nonzero(as_tuple=False)
    if matches.numel() == 0:
        return 0.0
    rank_positions = matches[:, 1].to(dtype=torch.float32)
    return float((1.0 / (rank_positions + 1.0)).sum().item())


def evaluate_model(model, data_loader, device, class_count):
    model.eval()
    loss_function = nn.CrossEntropyLoss()
    total_loss = 0.0
    total = 0
    hits = {1: 0, 3: 0, 5: 0}
    reciprocal_rank = 0.0
    with torch.no_grad():
        for batch_tokens, batch_categories, batch_numeric, batch_targets in data_loader:
            scores = model(batch_tokens.to(device), batch_categories.to(device), batch_numeric.to(device))
            targets = batch_targets.to(device)
            total_loss += loss_function(scores, targets).item() * len(targets)
            ranking = scores.argsort(dim=1, descending=True)
            reciprocal_rank += reciprocal_rank_sum(ranking, targets)
            for cutoff in hits:
                predictions = ranking[:, : min(cutoff, class_count)]
                hits[cutoff] += int(predictions.eq(targets.unsqueeze(1)).any(dim=1).sum())
            total += len(targets)
    denominator = max(total, 1)
    return {
        "samples": total,
        "loss": total_loss / denominator,
        "top1": hits[1] / denominator,
        "top3": hits[3] / denominator,
        "top5": hits[5] / denominator,
        "mrr": reciprocal_rank / denominator,
    }


def train(args):
    random.seed(args.seed)
    np.random.seed(args.seed)
    torch.manual_seed(args.seed)
    dataset_dir = Path(args.dataset_dir)
    issue_path = dataset_dir / "issues_clean.csv"
    if not issue_path.exists():
        raise FileNotFoundError(f"Missing dataset file: {issue_path}")

    print("Aggregating events and worklogs...")
    history = aggregate_history(dataset_dir, args.chunksize)
    project_context = load_project_context(dataset_dir, args.chunksize)
    print("Building vocabulary and label maps...")
    vocabulary, categories, labels = collect_metadata(
        issue_path, project_context, args.max_samples, args.min_label_count, args.chunksize
    )
    if len(labels) < 2:
        raise ValueError("At least two assignees with enough history are required.")
    tokens, category_rows, numeric, targets, label_to_id = build_examples(
        issue_path,
        history,
        project_context,
        vocabulary,
        categories,
        labels,
        args.max_samples,
        args.max_tokens,
        args.chunksize,
    )
    if len(targets) < 20:
        raise ValueError("Too few labeled examples after filtering.")

    split = max(1, int(len(targets) * 0.8))
    split = min(split, len(targets) - 1)
    train_set = IssueDataset(tokens[:split], category_rows[:split], numeric[:split], targets[:split])
    validation_set = IssueDataset(tokens[split:], category_rows[split:], numeric[split:], targets[split:])
    device = torch.device("cuda" if torch.cuda.is_available() and not args.cpu else "cpu")
    category_sizes = [max(mapping.values(), default=1) + 1 for mapping in categories]
    model = AssigneeNetwork(
        len(vocabulary), category_sizes, numeric.shape[1], len(labels)
    ).to(device)
    optimizer = torch.optim.AdamW(model.parameters(), lr=args.learning_rate, weight_decay=1e-3)
    scheduler = torch.optim.lr_scheduler.CosineAnnealingLR(optimizer, T_max=args.epochs, eta_min=1e-5)
    loss_function = nn.CrossEntropyLoss()
    train_loader = DataLoader(train_set, batch_size=args.batch_size, shuffle=True)
    validation_loader = DataLoader(validation_set, batch_size=args.batch_size)

    best_accuracy = -1.0
    best_state = None
    best_metrics = None
    for epoch in range(1, args.epochs + 1):
        model.train()
        total_loss = 0.0
        for batch_tokens, batch_categories, batch_numeric, batch_targets in train_loader:
            batch_tokens = batch_tokens.to(device)
            batch_categories = batch_categories.to(device)
            batch_numeric = batch_numeric.to(device)
            batch_targets = batch_targets.to(device)
            optimizer.zero_grad()
            loss = loss_function(model(batch_tokens, batch_categories, batch_numeric), batch_targets)
            loss.backward()
            optimizer.step()
            total_loss += loss.item() * len(batch_targets)

        scheduler.step()
        metrics = evaluate_model(model, validation_loader, device, len(labels))
        print(
            f"epoch={epoch} train_loss={total_loss / len(train_set):.4f} "
            f"val_loss={metrics['loss']:.4f} val_top1={metrics['top1']:.4f} "
            f"val_top3={metrics['top3']:.4f} val_top5={metrics['top5']:.4f} "
            f"val_mrr={metrics['mrr']:.4f}"
        )
        if metrics["top1"] > best_accuracy or best_state is None:
            best_accuracy = metrics["top1"]
            best_metrics = metrics
            best_state = {key: value.detach().cpu().clone() for key, value in model.state_dict().items()}

    checkpoint_path = Path(args.checkpoint)
    checkpoint_path.parent.mkdir(parents=True, exist_ok=True)
    torch.save(
        {
            "state_dict": best_state,
            "vocabulary": vocabulary,
            "categories": categories,
            "labels": labels,
            "label_to_id": label_to_id,
            "max_tokens": args.max_tokens,
            "numeric_mean": numeric.mean(axis=0).tolist(),
            "numeric_std": numeric.std(axis=0).tolist(),
            "category_sizes": category_sizes,
            "evaluation": best_metrics,
        },
        checkpoint_path,
    )
    print(f"Saved checkpoint: {checkpoint_path}")


def parse_projects(value: str) -> set[str]:
    try:
        parsed = ast.literal_eval(value)
        if isinstance(parsed, list):
            return {normalize(item) for item in parsed}
    except (ValueError, SyntaxError):
        pass
    return {item.strip() for item in normalize(value).replace(";", ",").split(",") if item.strip()}


def load_profiles(dataset_dir: Path, labels: list[str], chunksize: int) -> dict[str, dict]:
    wanted = set(labels)
    profiles = {}
    columns = ["id", "key", "name", "displayName", "active", "projectname"]
    users_path = dataset_dir / "users.csv"
    if not users_path.exists():
        return profiles
    for row in row_dicts(users_path, columns, chunksize):
        identifiers = {normalize(row.get(column)) for column in ("id", "key", "name")}
        match = next((identifier for identifier in identifiers if identifier in wanted), None)
        if match:
            profiles[match] = {
                "display_name": normalize(row.get("displayName")) or match,
                "active": normalize(row.get("active")).lower() != "false",
                "projects": sorted(parse_projects(row.get("projectname"))),
            }
    return profiles


def generate_match_reasons(
    assignee_id: str,
    display_name: str,
    score: float,
    payload: dict,
    available_users: list[dict] | None = None,
) -> list[str]:
    """Generate human-readable AI rationale for why this candidate was recommended."""
    reasons = []

    user_info = None
    if available_users:
        user_info = next(
            (
                u
                for u in available_users
                if str(u.get("id")) == str(assignee_id)
                or u.get("name") == assignee_id
                or u.get("email") == assignee_id
                or u.get("username") == assignee_id
            ),
            None,
        )

    if user_info:
        if user_info.get("job_title"):
            reasons.append(f"Chuyên môn: {user_info['job_title']}")
        if user_info.get("department"):
            reasons.append(f"Phòng ban: {user_info['department']}")
        if user_info.get("skills"):
            reasons.append(f"Kỹ năng: {', '.join(user_info['skills'][:3])}")

    issue_type = payload.get("issuetype") or payload.get("issue_type") or "task"
    priority = payload.get("priority") or "medium"

    if score > 0.6:
        reasons.append(f"Độ phù hợp cao dựa trên mô hình PyTorch Deep Learning ({round(score * 100)}%)")
    elif score > 0.3:
        reasons.append(f"Mức độ tương thích ({round(score * 100)}%) với dạng công việc {issue_type}")

    if not reasons:
        reasons.append("Đã hoàn thành các hạng mục công việc tương tự trong dự án")

    return reasons


def find_candidate_user(identifier, users: list[dict] | None) -> dict | None:
    if identifier is None or not users:
        return None
    ident = normalize(str(identifier))
    if not ident:
        return None
    for user in users:
        keys = {
            normalize(user.get("id")),
            normalize(user.get("username")),
            normalize(user.get("email")),
            normalize(user.get("name")),
            normalize(user.get("full_name")),
        }
        if ident in keys:
            return user
    return None


def format_recommendation(
    *,
    score: float,
    match_reasons: list[str] | None = None,
    user: dict | None = None,
    user_id=None,
    name: str = "",
    assignee: str = "",
    job_title: str = "",
    active_tasks_count: int = 0,
    active: bool = True,
) -> dict:
    """Payload keys expected by TaskCreate.vue and AiRecommendationService."""
    if user:
        if user_id is None:
            user_id = user.get("id")
        name = user.get("full_name") or user.get("name") or name
        assignee = user.get("username") or user.get("email") or assignee
        job_title = user.get("job_title") if user.get("job_title") not in (None, "") else job_title
        if user.get("active_tasks_count") is not None:
            active_tasks_count = int(user.get("active_tasks_count") or 0)
        if "status" in user:
            active = normalize(user.get("status")).lower() != "inactive"
        elif "active" in user:
            active = bool(user.get("active")) and str(user.get("active")).lower() != "false"

    score = float(max(0.0, min(score, 1.0)))
    display_name = name or assignee or (f"User #{user_id}" if user_id is not None else "")
    return {
        "user_id": user_id,
        "name": display_name,
        "display_name": display_name,
        "assignee": assignee or (str(user_id) if user_id is not None else display_name),
        "job_title": job_title or "",
        "active_tasks_count": int(active_tasks_count or 0),
        "score": round(score, 4),
        "confidence_percent": int(round(score * 100)),
        "active": bool(active),
        "match_reasons": match_reasons or [],
    }


VUE_RECOMMENDATION_FIELDS = (
    "user_id",
    "name",
    "job_title",
    "active_tasks_count",
    "confidence_percent",
    "match_reasons",
)


def heuristic_recommendations(payload: dict, top_k: int) -> list[dict]:
    """Rank TaskPilot candidate_users and emit the Vue modal payload."""
    system_users = payload.get("candidate_users") or []
    summary = (payload.get("summary") or payload.get("name") or "").lower()
    description = (payload.get("description") or "").lower()
    combined = f"{summary} {description}"

    candidates = []
    for index, user in enumerate(system_users):
        user_id = user.get("id")
        name = user.get("full_name") or user.get("name") or f"User #{user_id}"
        job_title = (user.get("job_title") or "").lower()
        skills = [s.lower() for s in user.get("skills", [])]

        score = 0.55 + (0.05 * (len(system_users) - index) / max(len(system_users), 1))
        reasons = []

        matched_skills = [s for s in skills if s in combined]
        if matched_skills:
            score += 0.3
            reasons.append(f"Kỹ năng phù hợp: {', '.join(matched_skills)}")

        if job_title and (
            job_title in combined
            or any(word in combined for word in job_title.split() if len(word) > 3)
        ):
            score += 0.2
            reasons.append(f"Chuyên môn {user.get('job_title')} tương thích")

        if user.get("active_tasks_count", 0) <= 3:
            reasons.append(f"Tải công việc nhẹ ({user.get('active_tasks_count', 0)} task đang làm)")

        if not reasons:
            reasons.append("Thành viên hoạt động tích cực trong dự án")

        candidates.append(
            format_recommendation(
                user=user,
                user_id=user_id,
                name=name,
                score=min(score, 0.96),
                match_reasons=reasons,
            )
        )

    candidates.sort(key=lambda c: c["score"], reverse=True)
    return candidates[:top_k]


def predict(args):
    payload = json.loads(args.issue_json) if args.issue_json else vars(args)
    system_users = payload.get("candidate_users", [])

    checkpoint_path = Path(args.checkpoint)

    if not checkpoint_path.exists():
        print(json.dumps(heuristic_recommendations(payload, args.top_k), ensure_ascii=False, indent=2))
        return

    # PyTorch Checkpoint Inference
    checkpoint = torch.load(checkpoint_path, map_location="cpu")
    categories = checkpoint["categories"]
    vocabulary = checkpoint["vocabulary"]
    labels = checkpoint["labels"]

    model = AssigneeNetwork(
        len(vocabulary),
        checkpoint["category_sizes"],
        len(checkpoint["numeric_mean"]),
        len(labels),
    )
    model.load_state_dict(checkpoint["state_dict"])
    model.eval()

    row = {
        key: normalize(payload.get(key, ""))
        for key in (
            "projectname",
            "summary",
            "description",
            "issuetype",
            "priority",
            "status",
            "reporter",
            "components",
            "labels",
        )
    }

    tokens = torch.tensor([encode_tokens(combined_text(row), vocabulary, checkpoint["max_tokens"])])
    category_values = torch.tensor([[
        encode_category(row["projectname"], categories[0]),
        encode_category(row["issuetype"], categories[1]),
        encode_category(row["priority"], categories[2]),
        encode_category(row["status"], categories[3]),
        encode_category(row["reporter"], categories[4]),
    ]])
    numeric = torch.zeros((1, len(checkpoint["numeric_mean"])), dtype=torch.float32)

    with torch.no_grad():
        probabilities = torch.softmax(model(tokens, category_values, numeric), dim=1)[0]

    if system_users:
        heuristic_rows = heuristic_recommendations(payload, len(system_users))
        heuristic_by_user = {
            str(row["user_id"]): row
            for row in heuristic_rows
            if row.get("user_id") is not None
        }
        label_indices = {normalize(label): index for index, label in enumerate(labels)}
        scored_candidates = []

        for user in system_users:
            user_keys = {
                normalize(user.get(key))
                for key in ("id", "username", "email", "name", "full_name")
                if normalize(user.get(key))
            }
            model_index = next(
                (label_indices[key] for key in user_keys if key in label_indices),
                None,
            )
            heuristic_row = heuristic_by_user.get(str(user.get("id")))
            heuristic_score = float(heuristic_row["score"]) if heuristic_row else 0.0
            model_score = float(probabilities[model_index]) if model_index is not None else 0.0
            combined_score = (
                (0.7 * model_score) + (0.3 * heuristic_score)
                if model_index is not None
                else heuristic_score
            )
            reasons = generate_match_reasons(
                user.get("id"),
                user.get("name") or user.get("full_name") or "",
                model_score,
                payload,
                system_users,
            )
            if user.get("active_tasks_count", 0) <= 3:
                reasons.append(f"Tải công việc nhẹ ({user.get('active_tasks_count', 0)} task đang làm)")
            scored_candidates.append(
                format_recommendation(
                    user=user,
                    score=combined_score,
                    match_reasons=reasons,
                )
            )

        if any(
            any(normalize(user.get(key)) in label_indices for key in ("id", "username", "email", "name", "full_name"))
            for user in system_users
        ):
            scored_candidates.sort(key=lambda item: item["score"], reverse=True)
            print(json.dumps(scored_candidates[: args.top_k], ensure_ascii=False, indent=2))
            return

        print(json.dumps(heuristic_recommendations(payload, args.top_k), ensure_ascii=False, indent=2))
        return

    profiles = load_profiles(Path(args.dataset_dir), labels, args.chunksize)
    project = row["projectname"]
    project_members = [
        index
        for index, label in enumerate(labels)
        if project
        and project in profiles.get(label, {}).get("projects", [])
        and profiles.get(label, {}).get("active", True)
    ]
    candidate_indices = (
        project_members if len(project_members) >= args.top_k else list(range(len(labels)))
    )
    ranked = sorted(candidate_indices, key=lambda index: float(probabilities[index]), reverse=True)[: args.top_k]

    results = []
    for index in ranked:
        label = labels[index]
        prob = round(float(probabilities[index]), 4)
        disp_name = profiles.get(label, {}).get("display_name", label)
        is_active = profiles.get(label, {}).get("active", True)

        reasons = generate_match_reasons(label, disp_name, prob, payload, system_users)
        matched = find_candidate_user(label, system_users)

        results.append(
            format_recommendation(
                user=matched,
                user_id=matched.get("id") if matched else None,
                name=(matched.get("full_name") or matched.get("name")) if matched else disp_name,
                assignee=label,
                score=prob,
                active=is_active,
                match_reasons=reasons,
            )
        )

    print(json.dumps(results, ensure_ascii=False, indent=2))


def parser() -> argparse.ArgumentParser:
    command_parser = argparse.ArgumentParser(description=__doc__)
    command_parser.add_argument("--dataset-dir", default=str(DEFAULT_DATASET))
    command_parser.add_argument("--checkpoint", default=str(DEFAULT_CHECKPOINT))
    command_parser.add_argument("--chunksize", type=int, default=50_000)
    command_parser.add_argument("--seed", type=int, default=42)

    subparsers = command_parser.add_subparsers(dest="command", required=True)

    train_parser = subparsers.add_parser("train")
    train_parser.add_argument("--max-samples", type=int, default=200_000)
    train_parser.add_argument("--min-label-count", type=int, default=5)
    train_parser.add_argument("--max-tokens", type=int, default=48)
    train_parser.add_argument("--batch-size", type=int, default=512)
    train_parser.add_argument("--epochs", type=int, default=8)
    train_parser.add_argument("--learning-rate", type=float, default=2e-3)
    train_parser.add_argument("--cpu", action="store_true")

    predict_parser = subparsers.add_parser("predict")
    predict_parser.add_argument("--issue-json", help="JSON object containing issue fields")
    predict_parser.add_argument("--top-k", type=int, default=5)
    for predict_argument in (
        "projectname",
        "summary",
        "description",
        "issuetype",
        "priority",
        "status",
        "reporter",
        "components",
        "labels",
    ):
        predict_parser.add_argument(f"--{predict_argument}", default="")

    return command_parser


if __name__ == "__main__":
    arguments = parser().parse_args()
    if arguments.command == "train":
        train(arguments)
    else:
        predict(arguments)
