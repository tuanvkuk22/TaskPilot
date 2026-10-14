"""Serving with candidate_users must not import torch."""

from __future__ import annotations

import json
import subprocess
import sys
import unittest
from pathlib import Path

AI_DIR = Path(__file__).resolve().parent


class PredictFastPathTest(unittest.TestCase):
    def test_candidate_serving_does_not_import_torch(self):
        payload = {
            "summary": "Fix mysql leak",
            "candidate_users": [
                {
                    "id": 10,
                    "name": "Tuấn TP",
                    "username": "tuan",
                    "skills": ["mysql"],
                    "active_tasks_count": 1,
                }
            ],
        }
        script = r"""
import json, sys
from types import SimpleNamespace
from contextlib import redirect_stdout
from io import StringIO
from assignee_recommender import predict

payload = json.loads(sys.argv[1])
args = SimpleNamespace(
    issue_json=json.dumps(payload),
    top_k=3,
    checkpoint="models/assignee_recommender.pt",
    use_model=False,
)
buf = StringIO()
with redirect_stdout(buf):
    predict(args)
rows = json.loads(buf.getvalue())
assert rows and rows[0]["user_id"] == 10, rows
assert "torch" not in sys.modules, "torch was imported"
assert "pandas" not in sys.modules, "pandas was imported"
print("ok")
"""
        completed = subprocess.run(
            [sys.executable, "-c", script, json.dumps(payload)],
            cwd=str(AI_DIR),
            capture_output=True,
            text=True,
            check=False,
        )
        self.assertEqual(completed.returncode, 0, completed.stderr)
        self.assertIn("ok", completed.stdout)
