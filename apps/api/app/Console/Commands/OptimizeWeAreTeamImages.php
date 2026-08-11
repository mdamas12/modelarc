<?php

namespace App\Console\Commands;

use App\Models\WeAreTeam;
use App\Services\WeAreTeamImageService;
use Illuminate\Console\Command;

class OptimizeWeAreTeamImages extends Command
{
    protected $signature = 'we-are:optimize-team-images';

    protected $description = 'Generate optimized display variants for Quiénes somos team images';

    public function handle(WeAreTeamImageService $images): int
    {
        $teams = WeAreTeam::query()->orderBy('id')->get();
        $bar = $this->output->createProgressBar($teams->count());
        $bar->start();

        foreach ($teams as $team) {
            $images->ensureDisplayVariant($team);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Team display images optimized.');

        return self::SUCCESS;
    }
}
