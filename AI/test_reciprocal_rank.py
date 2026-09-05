"""Regression tests for MRR rank extraction and recommendation payload shape."""

from __future__ import annotations

import json
import unittest

import torch

from assignee_recommender import (
    VUE_RECOMMENDATION_FIELDS,
    format_recommendation,
    heuristic_recommendations,
    predict,
    reciprocal_rank_sum,
)


class ReciprocalRankSumTest(unittest.TestCase):
    def test_known_ranks(self):
        scores = torch.tensor(
            [
                [0.1, 0.9, 0.3, 0.2],
                [0.8, 0.1, 0.4, 0.7],
                [0.2, 0.3, 0.1, 0.95],
            ]
        )
        targets = torch.tensor([1, 0, 2])
        ranking = scores.argsort(dim=1, descending=True)
        # ranks: 0, 0, 3 → 1 + 1 + 0.25
        self.assertAlmostEqual(reciprocal_rank_sum(ranking, targets), 2.25)

    def test_nonzero_rows_are_2d_rank_is_column_1(self):
        ranking = torch.tensor([[1, 2, 0], [2, 0, 1]])
        targets = torch.tensor([0, 2])
        matches = ranking.eq(targets.unsqueeze(1)).nonzero(as_tuple=False)
        self.assertEqual(tuple(matches.shape), (2, 2))
        self.assertEqual(matches[0].ndim, 1)
        unpacked = next(iter(matches))
        row_index, position = unpacked
        self.assertEqual(row_index.ndim, 0)
        self.assertEqual(position.ndim, 0)
        # ndim > 1 is never true for either the packed row or the unpacked rank.
        self.assertFalse(matches[0].ndim > 1)
        self.assertFalse(position.ndim > 1)
        self.assertEqual(int(matches[:, 1][0]), int(position))

    def test_packed_row_cannot_use_float_position_plus_one(self):
        ranking = torch.tensor([[1, 0]])
        targets = torch.tensor([0])
        packed_row = ranking.eq(targets.unsqueeze(1)).nonzero(as_tuple=False)[0]
        with self.assertRaises(ValueError):
            float(packed_row + 1)

    def test_empty_matches(self):
        ranking = torch.tensor([[0, 1]])
        targets = torch.tensor([5])
        self.assertEqual(reciprocal_rank_sum(ranking, targets), 0.0)

    def test_matches_argmax_ranks(self):
        torch.manual_seed(0)
        scores = torch.randn(16, 11)
        targets = torch.randint(0, 11, (16,))
        ranking = scores.argsort(dim=1, descending=True)
        ranks = ranking.eq(targets.unsqueeze(1)).to(dtype=torch.float32).argmax(dim=1)
        expected = float((1.0 / (ranks + 1).to(dtype=torch.float32)).sum().item())
        self.assertAlmostEqual(reciprocal_rank_sum(ranking, targets), expected)


class RecommendationPayloadTest(unittest.TestCase):
    def test_heuristic_includes_vue_fields(self):
        payload = format_recommendation(
            user={
                "id": 10,
                "full_name": "Tuấn TP",
                "username": "tuan",
                "job_title": "Backend Engineer",
                "active_tasks_count": 2,
                "status": "active",
            },
            score=0.85,
            match_reasons=["Kỹ năng phù hợp: php"],
        )
        for field in VUE_RECOMMENDATION_FIELDS:
            self.assertIn(field, payload)
        self.assertEqual(payload["user_id"], 10)
        self.assertEqual(payload["name"], "Tuấn TP")
        self.assertEqual(payload["job_title"], "Backend Engineer")
        self.assertEqual(payload["active_tasks_count"], 2)
        self.assertEqual(payload["confidence_percent"], 85)

    def test_pytorch_path_without_matched_user_still_has_name(self):
        payload = format_recommendation(
            name="bob",
            assignee="bob",
            score=0.42,
            active=True,
            match_reasons=["Mức độ tương thích"],
        )
        self.assertEqual(payload["name"], "bob")
        self.assertEqual(payload["confidence_percent"], 42)
        self.assertEqual(payload["job_title"], "")
        self.assertEqual(payload["active_tasks_count"], 0)

    def test_heuristic_recommendations_match_vue_contract(self):
        rows = heuristic_recommendations(
            {
                "summary": "Fix mysql leak",
                "description": "database connection",
                "candidate_users": [
                    {
                        "id": 10,
                        "name": "Tuấn TP",
                        "username": "tuan",
                        "job_title": "Backend Engineer",
                        "skills": ["php", "mysql"],
                        "status": "active",
                        "active_tasks_count": 2,
                    }
                ],
            },
            top_k=3,
        )
        self.assertEqual(len(rows), 1)
        row = rows[0]
        for field in VUE_RECOMMENDATION_FIELDS:
            self.assertIn(field, row)
        self.assertEqual(row["user_id"], 10)
        self.assertEqual(row["name"], "Tuấn TP")
        self.assertEqual(row["job_title"], "Backend Engineer")
        self.assertEqual(row["active_tasks_count"], 2)
        self.assertIsInstance(row["confidence_percent"], int)

    def test_predict_stdout_includes_vue_fields(self):
        from contextlib import redirect_stdout
        from io import StringIO
        from types import SimpleNamespace

        args = SimpleNamespace(
            issue_json=json.dumps(
                {
                    "summary": "Fix mysql leak",
                    "candidate_users": [
                        {
                            "id": 7,
                            "full_name": "Lan",
                            "username": "lan",
                            "job_title": "QA",
                            "skills": ["mysql"],
                            "status": "active",
                            "active_tasks_count": 0,
                        }
                    ],
                }
            ),
            checkpoint="/tmp/taskpilot-missing-checkpoint.pt",
            top_k=5,
            dataset_dir="AI/dataset",
            chunksize=1000,
        )
        buf = StringIO()
        with redirect_stdout(buf):
            predict(args)
        rows = json.loads(buf.getvalue())
        self.assertTrue(rows)
        for field in VUE_RECOMMENDATION_FIELDS:
            self.assertIn(field, rows[0])


if __name__ == "__main__":
    unittest.main()
