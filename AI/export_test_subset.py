#!/usr/bin/env python3
"""Export a compact JSON sample of the holdout test issues for Laravel import.

The training/eval pipeline uses the first 200_000 labeled issues, then the last 20%
as an independent holdout set. This script walks that same assigned-issue stream,
keeps a few busy projects from the holdout region, and writes users/projects/issues
small enough to load into TaskPilot for AI UI testing.
"""

from __future__ import annotations

import argparse
import ast
import json
import re
import sys
from collections import Counter, defaultdict
from pathlib import Path

import pandas as pd

AI_DIR = Path(__file__).resolve().parent
DEFAULT_DATASET = AI_DIR / "dataset"
DEFAULT_CHECKPOINT = AI_DIR / "models" / "assignee_recommender.pt"

EMPTY = {"", "nan", "none", "null", "unassigned", "n/a"}
DONE_STATUSES = {
    "closed",
    "resolved",
    "done",
    "completed",
    "fixed",
    "cancelled",
    "canceled",
    "won't fix",
    "cannot reproduce",
    "duplicate",
    "invalid",
}


def normalize(value) -> str:
    if value is None:
        return ""
    text = str(value).strip()
    return "" if text.lower() in EMPTY else text


def parse_listy(value) -> list[str]:
    text = normalize(value)
    if not text:
        return []
    try:
        parsed = ast.literal_eval(text)
        if isinstance(parsed, (list, tuple, set)):
            return [normalize(item) for item in parsed if normalize(item)]
    except (ValueError, SyntaxError, MemoryError):
        pass
    return [part.strip() for part in re.split(r"[/|,;]+", text) if normalize(part)]


def is_open_status(status: str) -> bool:
    return normalize(status).lower() not in DONE_STATUSES


def load_label_set(checkpoint_path: Path) -> set[str]:
    if not checkpoint_path.exists():
        return set()
    try:
        import torch

        checkpoint = torch.load(checkpoint_path, map_location="cpu")
        labels = checkpoint.get("labels") or []
        return {normalize(label) for label in labels if normalize(label)}
    except Exception as exc:  # noqa: BLE001
        print(f"warn: could not read checkpoint labels ({exc})", file=sys.stderr)
        return set()


def csv_chunks(path: Path, columns: list[str], chunksize: int = 50_000):
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


def load_projects(dataset_dir: Path) -> dict[str, dict]:
    path = dataset_dir / "project_clean.csv"
    projects = {}
    if not path.exists():
        return projects
    columns = ["key", "name", "description", "projectCategory", "lead", "components"]
    for chunk in csv_chunks(path, columns, 2_000):
        for row in chunk.itertuples(index=False):
            data = dict(zip(chunk.columns, row))
            key = normalize(data.get("key"))
            name = normalize(data.get("name"))
            record = {
                "key": key or name,
                "name": name or key,
                "description": (normalize(data.get("description")) or "")[:2000],
                "category": normalize(data.get("projectCategory")),
                "lead": normalize(data.get("lead")),
                "components": parse_listy(data.get("components")),
            }
            if key:
                projects[key] = record
            if name:
                projects[name] = record
    return projects


def collect_holdout_issues(
    issue_path: Path,
    *,
    limit: int,
    max_projects: int,
    max_assigned: int,
    labels: set[str],
) -> list[dict]:
    columns = [
        "key",
        "_id",
        "projectname",
        "summary",
        "issuetype",
        "priority",
        "status",
        "assignee",
        "reporter",
        "components",
        "labels",
        "created",
        "duedate",
    ]
    split_at = max(1, int(max_assigned * 0.8))
    open_target = max(40, limit // 2)
    closed_target = max(0, limit - open_target)
    open_projects: list[str] = []
    open_issues: list[dict] = []
    closed_issues: list[dict] = []
    assigned_seen = 0

    print(
        f"Scanning holdout issues (skip first {split_at:,} assigned, cap {max_assigned:,})...",
        file=sys.stderr,
        flush=True,
    )

    for chunk in csv_chunks(issue_path, columns):
        for values in chunk.itertuples(index=False, name=None):
            row = dict(zip(chunk.columns, values))
            assignee = normalize(row.get("assignee"))
            if not assignee:
                continue
            if labels and assignee not in labels:
                continue
            assigned_seen += 1
            if assigned_seen <= split_at:
                continue
            if assigned_seen > max_assigned:
                break

            project = normalize(row.get("projectname"))
            if not project:
                continue
            if project not in open_projects:
                if len(open_projects) >= max_projects:
                    continue
                open_projects.append(project)

            issue = {
                "key": normalize(row.get("key")) or normalize(row.get("_id")),
                "projectname": project,
                "summary": (normalize(row.get("summary")) or "Untitled issue")[:255],
                "issuetype": normalize(row.get("issuetype")) or "task",
                "priority": normalize(row.get("priority")) or "medium",
                "status": normalize(row.get("status")) or "Open",
                "assignee": assignee,
                "reporter": normalize(row.get("reporter")),
                "components": parse_listy(row.get("components")),
                "labels": parse_listy(row.get("labels")),
                "created": normalize(row.get("created")),
                "duedate": normalize(row.get("duedate")),
            }
            if is_open_status(issue["status"]):
                if len(open_issues) < open_target:
                    open_issues.append(issue)
            elif len(closed_issues) < closed_target:
                closed_issues.append(issue)

        if assigned_seen > max_assigned:
            break
        if len(open_issues) >= open_target and len(closed_issues) >= closed_target:
            break
        if assigned_seen % 50_000 == 0:
            print(
                f"  assigned={assigned_seen:,} open={len(open_issues)} closed={len(closed_issues)} projects={len(open_projects)}",
                file=sys.stderr,
                flush=True,
            )

    issues = open_issues + closed_issues
    print(
        f"Collected {len(issues)} issues from {len({item['projectname'] for item in issues})} projects "
        f"(open={len(open_issues)}, closed={len(closed_issues)}, assigned_seen={assigned_seen:,}).",
        file=sys.stderr,
        flush=True,
    )
    return issues


def collect_training_history(
    issue_path: Path,
    project_keys: set[str],
    exclude_keys: set[str],
    *,
    preferred_assignees: dict[str, set[str]],
    per_person: int,
    per_project: int,
    max_assigned: int,
) -> list[dict]:
    """Assigned training-split issues, preferring people who appear in the holdout test pool."""
    if per_project <= 0 or not project_keys:
        return []
    columns = [
        "key",
        "_id",
        "projectname",
        "summary",
        "issuetype",
        "priority",
        "status",
        "assignee",
        "reporter",
        "components",
        "labels",
        "created",
        "duedate",
    ]
    split_at = max(1, int(max_assigned * 0.8))
    buckets: dict[str, list[dict]] = {key: [] for key in project_keys}
    person_counts: dict[str, Counter] = {key: Counter() for key in project_keys}
    assigned_seen = 0
    print(
        f"Collecting training history (up to {per_person}/person, {per_project}/project)...",
        file=sys.stderr,
        flush=True,
    )
    for chunk in csv_chunks(issue_path, columns):
        for values in chunk.itertuples(index=False, name=None):
            row = dict(zip(chunk.columns, values))
            assignee = normalize(row.get("assignee"))
            if not assignee:
                continue
            assigned_seen += 1
            if assigned_seen > split_at:
                break
            project = normalize(row.get("projectname"))
            if project not in buckets or len(buckets[project]) >= per_project:
                continue
            key = normalize(row.get("key")) or normalize(row.get("_id"))
            if key in exclude_keys:
                continue
            preferred = preferred_assignees.get(project) or set()
            if assignee in preferred:
                if person_counts[project][assignee] >= per_person:
                    continue
            else:
                still_need_preferred = any(
                    person_counts[project][name] < per_person for name in preferred
                )
                if still_need_preferred:
                    continue
            buckets[project].append(
                {
                    "key": key,
                    "projectname": project,
                    "summary": (normalize(row.get("summary")) or "Untitled issue")[:255],
                    "issuetype": normalize(row.get("issuetype")) or "task",
                    "priority": normalize(row.get("priority")) or "medium",
                    "status": normalize(row.get("status")) or "Closed",
                    "assignee": assignee,
                    "reporter": normalize(row.get("reporter")),
                    "components": parse_listy(row.get("components")),
                    "labels": parse_listy(row.get("labels")),
                    "created": normalize(row.get("created")),
                    "duedate": normalize(row.get("duedate")),
                    "leave_unassigned": False,
                }
            )
            person_counts[project][assignee] += 1
        if assigned_seen > split_at:
            break
        if all(len(items) >= per_project for items in buckets.values()):
            break

    history = [item for items in buckets.values() for item in items]
    print(
        f"Collected {len(history)} training-history issues "
        f"({sum(1 for items in buckets.values() if items)}/{len(buckets)} projects filled).",
        file=sys.stderr,
        flush=True,
    )
    return history


def mark_unassigned(issues: list[dict], percent: int) -> None:
    open_issues = [issue for issue in issues if is_open_status(issue["status"])]
    want = min(len(open_issues), max(0, int(round(len(issues) * percent / 100.0))))
    for issue in issues:
        issue["leave_unassigned"] = False
    for issue in open_issues[:want]:
        issue["leave_unassigned"] = True


def load_users_for_projects(
    dataset_dir: Path,
    project_keys: set[str],
    required_names: set[str],
    extra_per_project: int,
) -> dict[str, dict]:
    path = dataset_dir / "users.csv"
    users: dict[str, dict] = {}
    extra_counts: Counter[str] = Counter()
    if not path.exists():
        return users

    columns = ["id", "key", "name", "displayName", "active", "projectname"]
    print("Matching users from users.csv...", file=sys.stderr, flush=True)
    for chunk in csv_chunks(path, columns, 20_000):
        for values in chunk.itertuples(index=False, name=None):
            row = dict(zip(chunk.columns, values))
            username = normalize(row.get("name")) or normalize(row.get("key")) or normalize(row.get("id"))
            if not username:
                continue
            projects = set(parse_listy(row.get("projectname")))
            needed = username in required_names
            extra_hits = [key for key in projects if key in project_keys and extra_counts[key] < extra_per_project]
            if not needed and not extra_hits:
                continue
            record = users.setdefault(
                username,
                {
                    "username": username,
                    "display_name": normalize(row.get("displayName")) or username,
                    "active": normalize(row.get("active")).lower() != "false",
                    "projects": set(),
                },
            )
            record["projects"].update(projects & project_keys)
            if needed:
                record["projects"].update(project_keys & projects)
            for key in extra_hits:
                extra_counts[key] += 1
                record["projects"].add(key)

    for name in required_names:
        users.setdefault(
            name,
            {
                "username": name,
                "display_name": name,
                "active": True,
                "projects": set(),
            },
        )
    return users


def attach_user_projects_and_skills(users: dict[str, dict], issues: list[dict], project_keys: set[str]) -> None:
    skills: dict[str, Counter] = defaultdict(Counter)
    seen_projects: dict[str, set[str]] = defaultdict(set)
    for issue in issues:
        project = issue["projectname"]
        for field in ("assignee", "reporter"):
            name = issue.get(field)
            if not name or name not in users:
                continue
            seen_projects[name].add(project)
            for skill in issue.get("components") or []:
                skills[name][skill[:80]] += 1

    for username, record in users.items():
        record["projects"] = sorted((record.get("projects") or set()) | seen_projects.get(username, set()) | set())
        record["projects"] = [key for key in record["projects"] if key in project_keys]
        record["skills"] = [name for name, _ in skills[username].most_common(6)]


def main() -> int:
    parser = argparse.ArgumentParser(description="Export holdout test issues for TaskPilot DB import.")
    parser.add_argument("--dataset-dir", default=str(DEFAULT_DATASET))
    parser.add_argument("--checkpoint", default=str(DEFAULT_CHECKPOINT))
    parser.add_argument("--output", required=True)
    parser.add_argument("--limit", type=int, default=300)
    parser.add_argument("--projects", type=int, default=8)
    parser.add_argument("--unassigned", type=int, default=40)
    parser.add_argument("--members", type=int, default=12)
    parser.add_argument("--history-per-project", type=int, default=24)
    parser.add_argument("--max-assigned", type=int, default=200_000)
    args = parser.parse_args()

    dataset_dir = Path(args.dataset_dir)
    issue_path = dataset_dir / "issues_clean.csv"
    if not issue_path.exists():
        print(f"Missing {issue_path}", file=sys.stderr)
        return 1

    labels = load_label_set(Path(args.checkpoint))
    if labels:
        print(f"Using {len(labels)} checkpoint assignee labels.", file=sys.stderr)

    issues = collect_holdout_issues(
        issue_path,
        limit=max(1, args.limit),
        max_projects=max(1, args.projects),
        max_assigned=max(1000, args.max_assigned),
        labels=labels,
    )
    if not issues:
        print("No holdout issues found.", file=sys.stderr)
        return 1

    mark_unassigned(issues, max(0, min(100, args.unassigned)))
    project_keys = {issue["projectname"] for issue in issues}
    exclude_keys = {issue["key"] for issue in issues if issue.get("key")}
    preferred = defaultdict(set)
    for issue in issues:
        assignee = issue.get("assignee")
        if assignee:
            preferred[issue["projectname"]].add(assignee)
    history = collect_training_history(
        issue_path,
        project_keys,
        exclude_keys,
        preferred_assignees=dict(preferred),
        per_person=8,
        per_project=max(0, args.history_per_project),
        max_assigned=max(1000, args.max_assigned),
    )
    issues.extend(history)
    required_names = {issue["assignee"] for issue in issues if issue.get("assignee")}
    required_names.update(issue["reporter"] for issue in issues if issue.get("reporter"))

    catalog = load_projects(dataset_dir)
    projects = []
    for key in sorted(project_keys):
        record = catalog.get(key) or {"key": key, "name": key, "description": "", "category": "", "lead": "", "components": []}
        projects.append(record)

    users = load_users_for_projects(dataset_dir, project_keys, required_names, max(0, args.members))
    attach_user_projects_and_skills(users, issues, project_keys)

    payload = {
        "source": "holdout_test_20pct",
        "max_assigned": args.max_assigned,
        "projects": projects,
        "users": [
            {
                "username": record["username"],
                "display_name": record["display_name"],
                "active": bool(record["active"]),
                "projects": record["projects"],
                "skills": record.get("skills") or [],
            }
            for record in users.values()
        ],
        "issues": issues,
    }

    output = Path(args.output)
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(payload, ensure_ascii=False), encoding="utf-8")
    print(
        f"Wrote {output} ({len(projects)} projects, {len(payload['users'])} users, {len(issues)} issues).",
        file=sys.stderr,
        flush=True,
    )
    return 0


if __name__ == "__main__":
    sys.exit(main())
