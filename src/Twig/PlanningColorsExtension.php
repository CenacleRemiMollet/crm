<?php

namespace App\Twig;

use App\Service\PlanningColors;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PlanningColorsExtension extends AbstractExtension
{
    private PlanningColors $planningColors;

    public function __construct(PlanningColors $planningColors)
    {
        $this->planningColors = $planningColors;
    }

    public function getFunctions(): array
    {
        return [
            // lesson color if set, else the discipline color (admin override, else CSS default); null = no color
            new TwigFunction('lesson_color', [$this, 'lessonColor']),
            new TwigFunction('discipline_color', [$this->planningColors, 'defaultFor']),
        ];
    }

    public function lessonColor($lesson): ?string
    {
        $color = is_object($lesson) ? ($lesson->color ?? null) : ($lesson['color'] ?? null);
        if (PlanningColors::isValidColor($color)) {
            return strtoupper($color);
        }
        $discipline = is_object($lesson) ? ($lesson->discipline ?? null) : ($lesson['discipline'] ?? null);
        return $this->planningColors->defaultFor($discipline);
    }
}
