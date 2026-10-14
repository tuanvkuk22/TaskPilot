#!/usr/bin/env python3
"""Dedicated Training Script for TaskPilot Deep Learning Assignee Recommender.

Run directly without extra arguments:
    python3 train.py
"""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

# Add parent AI directory to path if needed
AI_DIR = Path(__file__).resolve().parent
if str(AI_DIR) not in sys.path:
    sys.path.insert(0, str(AI_DIR))

from assignee_recommender import train


def main():
    parser = argparse.ArgumentParser(description="Train TaskPilot Deep Learning Assignee Recommender Model.")
    parser.add_argument("--dataset-dir", default=str(AI_DIR / "dataset"), help="Path to CSV dataset directory")
    parser.add_argument("--checkpoint", default=str(AI_DIR / "models" / "assignee_recommender.pt"), help="Output model checkpoint path")
    parser.add_argument("--epochs", type=int, default=15, help="Maximum training epochs (early stop may halt sooner)")
    parser.add_argument("--batch-size", type=int, default=256, help="Batch size for training")
    parser.add_argument("--learning-rate", type=float, default=0.0015, help="Learning rate (AdamW)")
    parser.add_argument("--dropout", type=float, default=0.3, help="MLP dropout probability")
    parser.add_argument("--weight-decay", type=float, default=5e-3, help="AdamW weight decay")
    parser.add_argument("--label-smoothing", type=float, default=0.05, help="Cross-entropy label smoothing")
    parser.add_argument("--patience", type=int, default=3, help="Epochs without val_loss improvement before stop")
    parser.add_argument("--min-delta", type=float, default=0.005, help="Minimum val_loss improvement to reset patience")
    parser.add_argument("--grad-clip", type=float, default=1.0, help="Gradient clip max-norm (0 disables)")
    parser.add_argument("--max-samples", type=int, default=200_000, help="Maximum training samples to load")
    parser.add_argument("--min-label-count", type=int, default=10, help="Minimum assignments per assignee")
    parser.add_argument("--max-tokens", type=int, default=48, help="Maximum text token length")
    parser.add_argument("--chunksize", type=int, default=50_000, help="CSV reader chunk size")
    parser.add_argument("--seed", type=int, default=42, help="Random seed for reproducibility")
    parser.add_argument("--cpu", action="store_true", help="Force CPU mode")

    args = parser.parse_args()

    print("=" * 70)
    print("🚀 TASKPILOT AI - HỦẤN LUYỆN MÔ HÌNH HỌC SÂU (DEEP LEARNING TRAINER)")
    print("=" * 70)
    print(f"  • Thư mục Dataset  : {args.dataset_dir}")
    print(f"  • Thư mục Output   : {args.checkpoint}")
    print(f"  • Số Epochs        : {args.epochs} (early stop patience={args.patience})")
    print(f"  • Batch Size       : {args.batch_size}")
    print(f"  • Learning Rate    : {args.learning_rate}")
    print(f"  • Dropout          : {args.dropout}")
    print(f"  • Weight Decay     : {args.weight_decay}")
    print(f"  • Label Smoothing  : {args.label_smoothing}")
    print(f"  • Max Samples      : {args.max_samples:,}")
    print(f"  • Min Label Count  : {args.min_label_count}")
    print("=" * 70)

    train(args)

    print("\n✅ Huấn luyện hoàn tất! Checkpoint đã được lưu tại:", args.checkpoint)


if __name__ == "__main__":
    main()
