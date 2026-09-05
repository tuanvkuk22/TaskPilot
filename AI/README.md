# Assignee recommender

`assignee_recommender.py` trains a PyTorch deep-learning classifier that ranks suitable assignees for an issue. The model is a multi-layer perceptron (MLP) with:

- Mean-pooled token embeddings for issue and project text.
- Separate embeddings for project, issue type, priority, status, and reporter.
- Four numeric features aggregated from events and worklogs.
- Two fully connected hidden layers with ReLU and dropout, followed by a multi-class assignee classifier.

The training features use:

- Issue text and metadata from `issues_clean.csv`.
- Project metadata through the issue project/category fields.
- Event counts and status/assignee change counts from `events.csv`.
- Worklog count and logged seconds from `worklogs.csv`.
- User activity and project membership from `users.csv` when serving predictions.

The CSV files are read in chunks. By default, training uses the first 200,000 assigned issues and assignees with at least five historical assignments, which keeps a first training run practical. Increase `--max-samples` for a larger run.

## Train

From the repository root:

```bash
pip install -r requirements.txt
python3 AI/assignee_recommender.py train
```

The checkpoint is written to `AI/models/assignee_recommender.pt`. Useful options include:

```bash
python3 AI/assignee_recommender.py train \
  --max-samples 500000 \
  --epochs 12 \
  --batch-size 512
```

During training, the last 20% of the selected labeled issues are used as a validation set. Each epoch reports:

- `Top-1`, `Top-3`, and `Top-5`: whether the correct assignee is in the first 1, 3, or 5 recommendations.
- `MRR` (Mean Reciprocal Rank): rewards putting the correct assignee near the top of the ranking.
- Validation cross-entropy loss.

The best validation metrics are saved in the checkpoint under the `evaluation` field. This makes the reported result reproducible for the exact trained model and validation split.

## Recommend an assignee

```bash
python3 AI/assignee_recommender.py predict \
  --projectname JELLY \
  --summary "Fix variable storage" \
  --description "Simplify the scope integration" \
  --issuetype Bug \
  --priority Major \
  --reporter bob \
  --top-k 5
```

The command prints JSON containing the username, display name, model score, and active status. For application integration, the same fields can be supplied as one JSON object with `--issue-json`.
