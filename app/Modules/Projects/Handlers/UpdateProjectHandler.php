<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Project;
use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;

class UpdateProjectHandler
{
    /**
     * Create a new handler instance.
     *
     * @param ProjectRepository $repository
     */
    public function __construct(
        private readonly ProjectRepository $repository
    ) {
    }

    /**
     * Execute project update use case.
     *
     * @param Project $project
     * @param array $data
     *
     * @return bool
     */
    public function handle(Project $project, array $data): bool
    {
        return $this->repository->update($project, [
            'name' => $data['name'] ?? $project->name,
            'image' => array_key_exists('image', $data) ? $data['image'] : $project->image,
            'description' => $data['description'] ?? $project->description,
            'status' => $data['status'] ?? $project->status,
            'priority' => $data['priority'] ?? $project->priority,
            'start_date' => $data['start_date'] ?? $project->start_date,
            'end_date' => $data['end_date'] ?? $project->end_date,
            'budget' => $data['budget'] ?? $project->budget,
            'company_id' => array_key_exists('company_id', $data) ? ($data['company_id'] ?: null) : $project->company_id,
            'category_id' => $data['category_id'] ?? $project->category_id,
            'team_id' => $data['team_id'] ?? $project->team_id,
            'objective' => $data['objective'] ?? $project->objective,
            'project_type' => $data['project_type'] ?? $project->project_type,
            'workflow_config' => array_key_exists('workflow_config', $data) ? (is_string($data['workflow_config']) ? json_decode($data['workflow_config'], true) : $data['workflow_config']) : $project->workflow_config,
        ]);

    }
}
