#!/usr/bin/env python3
"""Dedicated Evaluation Script for TaskPilot Deep Learning Assignee Recommender.

Run directly without extra arguments:
    python3 evaluate.py
"""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

import torch

AI_DIR = Path(__file__).resolve().parent
if str(AI_DIR) not in sys.path:
    sys.path.insert(0, str(AI_DIR))

from assignee_recommender import (
    AssigneeNetwork,
    IssueDataset,
    aggregate_history,
    build_examples,
    collect_metadata,
    evaluate_model,
    load_project_context,
)
from torch.utils.data import DataLoader


def main():
    parser = argparse.ArgumentParser(description="Evaluate TaskPilot Deep Learning Assignee Recommender Model.")
    parser.add_argument("--checkpoint", default=str(AI_DIR / "models" / "assignee_recommender.pt"), help="Checkpoint file path")
    parser.add_argument("--dataset-dir", default=str(AI_DIR / "dataset"), help="CSV dataset directory")
    parser.add_argument("--output-json", default=str(AI_DIR / "evaluation" / "evaluation_report.json"), help="Path to save output evaluation report JSON")
    parser.add_argument("--batch-size", type=int, default=512, help="Batch size")
    parser.add_argument("--cpu", action="store_true", help="Force CPU mode")

    args = parser.parse_args()
    checkpoint_path = Path(args.checkpoint)

    print("=" * 70)
    print("📊 TASKPILOT AI - ĐÁNH GIÁ MÔ HÌNH HỌC SÂU (MODEL EVALUATOR)")
    print("=" * 70)
    print(f"  • File Checkpoint : {checkpoint_path}")

    if not checkpoint_path.exists():
        print(f"❌ Không tìm thấy checkpoint tại {checkpoint_path}. Vui lòng chạy `python3 train.py` trước.")
        sys.exit(1)

    checkpoint = torch.load(checkpoint_path, map_location="cpu")
    eval_info = checkpoint.get("evaluation", {})

    print("\n--- [ TỔNG QUAN CHECKPOINT ĐÃ HUẤN LUYỆN ] ---")
    print(f"  • Số lượng nhãn (Assignees)  : {len(checkpoint.get('labels', []))}")
    print(f"  • Kích thước Từ vựng (Vocab) : {len(checkpoint.get('vocabulary', {}))}")
    print(f"  • Số token tối đa (Max tokens): {checkpoint.get('max_tokens', 48)}")

    if eval_info:
        print("\n--- [ KẾT QUẢ ĐÁNH GIÁ TRÊN TẬP VALIDATION ] ---")
        print(f"  • Tổng mẫu thử nghiệm (Samples) : {eval_info.get('samples', 0):,}")
        print(f"  • Cross-Entropy Loss             : {eval_info.get('loss', 0.0):.4f}")
        print(f"  • Top-1 Accuracy (Đúng vị trí 1) : {eval_info.get('top1', 0.0) * 100:.2f}%")
        print(f"  • Top-3 Accuracy (Đúng trong Top3): {eval_info.get('top3', 0.0) * 100:.2f}%")
        print(f"  • Top-5 Accuracy (Đúng trong Top5): {eval_info.get('top5', 0.0) * 100:.2f}%")
        print(f"  • MRR (Mean Reciprocal Rank)     : {eval_info.get('mrr', 0.0):.4f}")

    # Re-evaluating on CSV test set if dataset exists
    dataset_dir = Path(args.dataset_dir)
    issue_path = dataset_dir / "issues_clean.csv"

    if issue_path.exists():
        print("\n⏳ Đang tiến hành đánh giá chi tiết trên tập dữ liệu CSV...")
        try:
            history = aggregate_history(dataset_dir, 50_000)
            project_context = load_project_context(dataset_dir, 50_000)
            vocabulary = checkpoint["vocabulary"]
            categories = checkpoint["categories"]
            labels = checkpoint["labels"]

            tokens, category_rows, numeric, targets, _ = build_examples(
                issue_path,
                history,
                project_context,
                vocabulary,
                categories,
                labels,
                200_000,
                checkpoint["max_tokens"],
                50_000,
            )

            if len(targets) > 0:
                split = max(1, int(len(targets) * 0.8))
                test_tokens = tokens[split:]
                test_cat = category_rows[split:]
                test_num = numeric[split:]
                test_targets = targets[split:]

                dataset = IssueDataset(test_tokens, test_cat, test_num, test_targets)
                loader = DataLoader(dataset, batch_size=args.batch_size)
                device = torch.device("cuda" if torch.cuda.is_available() and not args.cpu else "cpu")
                model = AssigneeNetwork(
                    len(vocabulary),
                    checkpoint["category_sizes"],
                    len(checkpoint["numeric_mean"]),
                    len(labels),
                ).to(device)
                model.load_state_dict(checkpoint["state_dict"])

                fresh_metrics = evaluate_model(model, loader, device, len(labels))
                print("\n--- [ KẾT QUẢ ĐÁNH GIÁ TRÊN TẬP TEST ĐỘC LẬP HOÀN TOÀN (HOLDOUT TEST SET - 20%) ] ---")
                print(f"  • Số mẫu Test độc lập (Unseen Samples): {fresh_metrics['samples']:,}")
                print(f"  • Top-1 Accuracy (Đúng vị trí 1)        : {fresh_metrics['top1'] * 100:.2f}%")
                print(f"  • Top-3 Accuracy (Đúng trong Top 3)     : {fresh_metrics['top3'] * 100:.2f}%")
                print(f"  • Top-5 Accuracy (Đúng trong Top 5)     : {fresh_metrics['top5'] * 100:.2f}%")
                print(f"  • MRR (Mean Reciprocal Rank)            : {fresh_metrics['mrr']:.4f}")

                eval_info = fresh_metrics
        except Exception as e:
            print(f"⚠️ Không thể chạy đánh giá CSV động: {e}")

    # Save output evaluation JSON report
    output_path = Path(args.output_json)
    output_path.parent.mkdir(parents=True, exist_ok=True)
    report_data = {
        "checkpoint": str(checkpoint_path),
        "labels_count": len(checkpoint.get("labels", [])),
        "vocabulary_size": len(checkpoint.get("vocabulary", {})),
        "metrics": eval_info,
    }
    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(report_data, f, ensure_ascii=False, indent=2)

    print(f"\n✅ Đã lưu báo cáo đánh giá tại: {output_path}")
    print("=" * 70)


if __name__ == "__main__":
    main()
