# Data Quality Assessment Report

## Total Tables: 4
## Total Features: 63

## AI Readiness
- Overall Score: **86.1/100**
- Status: Ready for AI

## Keep
- users._id (score=100.0)
- users.active (score=100.0)
- users.displayName (score=100.0)
- users.id (score=100.0)
- users.key (score=100.0)
- users.name (score=100.0)
- users.projectname (score=100.0)
- users.timeZone (score=100.0)
- worklogs._id (score=100.0)
- worklogs.author (score=100.0)
- worklogs.comment (score=99.8)
- worklogs.created (score=100.0)
- worklogs.id (score=100.0)
- worklogs.issue (score=100.0)
- worklogs.issueId (score=100.0)
- worklogs.projectname (score=100.0)
- worklogs.started (score=100.0)
- worklogs.timeSpent (score=100.0)
- worklogs.timeSpentSeconds (score=100.0)
- worklogs.updateAuthor (score=100.0)
- worklogs.updated (score=100.0)
- issues_clean._id (score=100.0)
- issues_clean.key (score=100.0)
- issues_clean.projectname (score=100.0)
- issues_clean.summary (score=100.0)
- issues_clean.description (score=100.0)
- issues_clean.issuetype (score=100.0)
- issues_clean.priority (score=100.0)
- issues_clean.status (score=100.0)
- issues_clean.resolution (score=83.79)
- issues_clean.assignee (score=100.0)
- issues_clean.reporter (score=100.0)
- issues_clean.created (score=100.0)
- issues_clean.updated (score=100.0)
- issues_clean.resolutiondate (score=83.79)
- issues_clean.issue_links (score=21.27)
- issues_clean.components (score=100.0)
- issues_clean.labels (score=47.66)
- issues_clean.fixVersions (score=92.52)
- issues_clean.attachmentcount (score=100.0)
- issues_clean.votes (score=100.0)
- issues_clean.watches (score=100.0)
- issues_clean.workratio (score=100.0)
- events._id (score=100.0)
- events.author (score=100.0)
- events.created (score=100.0)
- events.id (score=100.0)
- events.issue (score=100.0)
- events.projectname (score=100.0)
- events.field (score=100.0)
- events.fieldtype (score=99.9)
- events.from (score=57.87)
- events.fromString (score=61.99)
- events.to (score=80.03)
- events.toString (score=86.26)

## Target
- issues_clean.assignee

## Optional
- issues_clean.duedate
- issues_clean.parent_key
- issues_clean.timeestimate
- issues_clean.timeoriginalestimate
- issues_clean.timespent

## Ignore
- users.avatarUrls
- users.self
- worklogs.self

## Low Completeness (<50%)
- issues_clean.duedate (0.75%)
- issues_clean.parent_key (8.99%)
- issues_clean.issue_links (21.27%)
- issues_clean.labels (17.66%)
- issues_clean.timeestimate (9.71%)
- issues_clean.timeoriginalestimate (1.97%)
- issues_clean.timespent (7.93%)

## Foreign Key
- worklogs.author (99.99%)
- worklogs.issue (100.0%)
- worklogs.issueId (100.0%)
- worklogs.updateAuthor (99.99%)
- issues_clean.assignee (100.0%)
- issues_clean.reporter (100.0%)
- events.author (100.0%)
- events.issue (100.0%)
