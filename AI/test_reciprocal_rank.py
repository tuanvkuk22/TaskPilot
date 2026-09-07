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
    validate_prediction_payload,
)


class PredictionValidationTest(unittest.TestCase):
    def test_rejects_malformed_structures(self):
        for payload in ([], {"candidate_users": "users"}, {"history": [None]},
                        {"candidate_users": [{"skills": "python"}]},
                        {"candidate_users": [{"active_tasks_count": -1}]},
                        {"summary": ["text"]}):
            with self.subTest(payload=payload), self.assertRaises(ValueError):
                validate_prediction_payload(payload, 5)

    def test_rejects_invalid_top_k(self):
        for top_k in (0, -1, 101, True):
            with self.subTest(top_k=top_k), self.assertRaises(ValueError):
                heuristic_recommendations({}, top_k)

    def test_unreadable_model_falls_back_to_heuristic(self):
        import argparse
        import io
        from contextlib import redirect_stdout
        from unittest.mock import patch

        payload = {"summary": "Fix mysql query", "candidate_users": [
            {"id": 1, "name": "Developer", "username": "dev", "skills": ["mysql"]}
        ]}
        args = argparse.Namespace(issue_json=json.dumps(payload), top_k=5, checkpoint="model.pt")
        with patch("assignee_recommender.Path.exists", return_value=True), \
                patch("assignee_recommender.torch.load", side_effect=OSError("unavailable")), \
                redirect_stdout(io.StringIO()) as output:
            predict(args)
        self.assertEqual(json.loads(output.getvalue()), heuristic_recommendations(payload, 5))

    def test_empty_candidates_are_valid(self):
        self.assertEqual(heuristic_recommendations({"candidate_users": []}, 5), [])


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

    def test_heuristic_ignores_list_order_and_generic_admin(self):
        rows = heuristic_recommendations(
            {
                "summary": "Ambari should work on nodes with puppet already installed",
                "projectname": "AMBARI",
                "components": "ambari-agent",
                "candidate_users": [
                    {"id": 1, "name": "Admin", "username": "", "job_title": "", "skills": []},
                    {
                        "id": 2,
                        "name": "Jitendra Nath Pandey",
                        "username": "jnp",
                        "job_title": "ambari-agent",
                        "skills": ["ambari-agent"],
                    },
                ],
            },
            top_k=3,
        )
        self.assertEqual(len(rows), 1)
        self.assertEqual(rows[0]["user_id"], 2)
        self.assertNotEqual(rows[0]["confidence_percent"], 60)
        self.assertLess(rows[0]["confidence_percent"], 100)

    def test_heuristic_test_skill_does_not_match_testing_substring(self):
        rows = heuristic_recommendations(
            {
                "summary": "Utility to initialize in memory action db for testing",
                "candidate_users": [
                    {
                        "id": 3,
                        "name": "Venkat Ranganathan",
                        "username": "venkatnrangan",
                        "job_title": "Test",
                        "skills": ["Test"],
                    }
                ],
            },
            top_k=3,
        )
        self.assertEqual(rows, [])

    def test_heuristic_uses_assignment_history(self):
        rows = heuristic_recommendations(
            {
                "summary": "ooziedb.sh should be invoked after setup",
                "projectname": "AMBARI",
                "candidate_users": [
                    {"id": 1, "name": "Admin", "username": "", "skills": []},
                    {"id": 2, "name": "Jitendra", "username": "jnp", "skills": []},
                ],
                "history": [
                    {"summary": "invoke ooziedb.sh after ambari setup", "assignee": "jnp", "issuetype": "bug"},
                ],
            },
            top_k=3,
        )
        self.assertEqual(rows[0]["user_id"], 2)

    def _ambari_users(self):
        return [
            {"id": 1, "name": "Yusaku Sako", "username": "u39kun", "job_title": "ambari-web", "skills": ["ambari-web"]},
            {
                "id": 2,
                "name": "Jitendra Nath Pandey",
                "username": "jnp",
                "job_title": "ambari-agent",
                "skills": ["ambari-agent", "ambari-server"],
            },
            {"id": 3, "name": "Mahadev Konar", "username": "mahadev", "job_title": "ambari-agent", "skills": ["ambari-agent"]},
        ]

    def _sparse_history(self):
        return [
            {
                "summary": "If possible, the Project Information link on the website should unfold by default",
                "assignee": "u39kun",
                "issuetype": "bug",
                "components": "",
            },
            {"summary": "Add jce policy file.", "assignee": "jnp", "issuetype": "bug", "components": ""},
            {
                "summary": "Ambari should work on nodes with puppet already installed.",
                "assignee": "mahadev",
                "issuetype": "bug",
                "components": "ambari-agent",
            },
        ]

    def test_project_name_overlap_is_not_100_percent(self):
        rows = heuristic_recommendations(
            {
                "summary": "Bug fix in jaxb serialization for maps.",
                "projectname": "AMBARI",
                "issuetype": "subtask",
                "candidate_users": self._ambari_users(),
                "history": self._sparse_history(),
            },
            top_k=3,
        )
        self.assertTrue(rows)
        self.assertLess(rows[0]["confidence_percent"], 100)
        self.assertLessEqual(rows[0]["confidence_percent"], 93)

    def test_singleton_match_never_reports_100(self):
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
                    }
                ],
            },
            top_k=3,
        )
        self.assertEqual(len(rows), 1)
        self.assertLess(rows[0]["confidence_percent"], 100)
        self.assertLessEqual(rows[0]["confidence_percent"], 93)

    def test_web_in_title_does_not_match_ambari_web_skill(self):
        rows = heuristic_recommendations(
            {
                "summary": "Create a key value get/put API for web UI to use for tmp storage.",
                "projectname": "AMBARI",
                "issuetype": "subtask",
                "candidate_users": self._ambari_users(),
                "history": self._sparse_history(),
            },
            top_k=3,
        )
        self.assertTrue(rows)
        for row in rows:
            if row["user_id"] == 1:
                self.assertFalse(any("Kỹ năng khớp nội dung: ambari-web" in reason for reason in row["match_reasons"]))

    def test_server_in_title_does_not_match_ambari_server_skill(self):
        rows = heuristic_recommendations(
            {
                "summary": "Make API authentication configurable at the server. For testing its useful to switch off authentication for the APIs",
                "projectname": "AMBARI",
                "issuetype": "subtask",
                "candidate_users": self._ambari_users(),
                "history": self._sparse_history(),
            },
            top_k=3,
        )
        self.assertTrue(rows)
        for row in rows:
            self.assertFalse(any("Kỹ năng khớp nội dung: ambari-server" in reason for reason in row["match_reasons"]))

    def test_ambari_web_component_still_ranks_web_developer(self):
        rows = heuristic_recommendations(
            {
                "summary": "Add Hadoop stack selection to the Installer",
                "projectname": "AMBARI",
                "issuetype": "subtask",
                "components": "ambari-web",
                "candidate_users": self._ambari_users(),
                "history": self._sparse_history(),
            },
            top_k=3,
        )
        self.assertTrue(rows)
        self.assertEqual(rows[0]["user_id"], 1)
        self.assertLess(rows[0]["confidence_percent"], 100)

    def test_generic_history_tokens_are_ignored(self):
        rows = heuristic_recommendations(
            {
                "summary": "ooziedb.sh should be invoked after setup.",
                "projectname": "AMBARI",
                "issuetype": "bug",
                "candidate_users": self._ambari_users(),
                "history": [
                    {
                        "summary": "Invoking browser back action on any step after the Add Hosts step",
                        "assignee": "u39kun",
                        "components": "",
                    }
                ],
            },
            top_k=3,
        )
        self.assertTrue(rows)
        self.assertNotEqual(rows[0]["user_id"], 1)

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
