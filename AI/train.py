#!/usr/bin/env python3
"""Dedicated Training Script for TaskPilot Learning to Rank (LTR) Assignee Recommender.
Uses off-the-shelf ColumnTransformer + LightGBM LGBMRanker (LambdaMART).

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

from assignee_recommender import train, DEFAULT_HYBRID_CHECKPOINT


def main():
    parser = argparse.ArgumentParser(
        description="Train TaskPilot Learning to Rank Assignee Recommender (ColumnTransformer + LambdaMART LGBMRanker)."
    )
    parser.add_argument("--dataset-dir", default=str(AI_DIR / "dataset"), help="Path to CSV dataset directory")
    parser.add_argument(
        "--checkpoint",
        default=str(DEFAULT_HYBRID_CHECKPOINT),
        help="Output model checkpoint path (.joblib)",
    )
    parser.add_argument(
        "--classifier",
        choices=["both", "all", "ranker", "lgbm_ranker", "mlp", "logistic_regression", "gradient_boosting"],
        default="both",
        help="Model engine to train (default: both, includes LambdaMART LGBMRanker + MLP + LR + GBC)",
    )
    parser.add_argument("--seed", type=int, default=42, help="Random seed for reproducibility")
    parser.add_argument("--legacy-torch", action="store_true", help="Train legacy PyTorch model if requested")

    args = parser.parse_args()

    train(args)


if __name__ == "__main__":
    main()
