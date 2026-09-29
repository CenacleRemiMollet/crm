<?php

namespace App\Service;

use App\Entity\ConfigurationProperty;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Planning colors (one per discipline), configurable in the admin (configuration_property "planning.color.<discipline>").
 * Defaults stay in assets/club/infos_club.css: only valid overrides are rendered.
 */
class PlanningColors
{
    public const PREFIX = 'planning.color.';

    // value that resets a discipline to its default color (a property value can't be blank)
    public const RESET_VALUE = 'default';

    // must match assets/club/infos_club.css ("CASE COLOR"); null = no color by default
    public const DEFAULTS = [
        'taekwondo' => '#E66F78',
        'taekwonkido' => '#8AB6F1',
        'hapkido' => '#9CE799',
        'sinkido' => '#F8FF8B',
        'khido' => '#F7A668',
        'gumdo' => null,
        'empty' => '#D1D1D1',
    ];

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function isValidColor(?string $color): bool
    {
        return $color !== null && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
    }

    /**
     * Validates a "planning.color.*" property, returns an error message or null if valid.
     */
    public static function validate(string $key, ?string $value): ?string
    {
        $discipline = substr($key, strlen(self::PREFIX));
        if (! array_key_exists($discipline, self::DEFAULTS)) {
            return 'Unknown discipline \''.$discipline.'\', expected one of: '.implode(', ', array_keys(self::DEFAULTS));
        }
        if ($value !== self::RESET_VALUE && ! self::isValidColor($value)) {
            return 'Invalid color \''.$value.'\', expected #RRGGBB or \''.self::RESET_VALUE.'\'';
        }
        return null;
    }

    /**
     * @return array<string, string> configured and valid colors, by discipline
     */
    public function getOverrides(): array
    {
        $properties = $this->em->getRepository(ConfigurationProperty::class)->findByStartsWith(self::PREFIX);
        $overrides = [];
        foreach ($properties as $property) {
            $discipline = substr($property->getPropertyKey(), strlen(self::PREFIX));
            $color = $property->getPropertyValue();
            // re-validated on read: the value is printed inside a <style> element
            if (array_key_exists($discipline, self::DEFAULTS) && self::isValidColor($color)) {
                $overrides[$discipline] = strtoupper($color);
            }
        }
        return $overrides;
    }

    /**
     * @return array<string, array{default: ?string, color: ?string, configured: bool}> for the admin
     */
    public function getAll(): array
    {
        $overrides = $this->getOverrides();
        $all = [];
        foreach (self::DEFAULTS as $discipline => $default) {
            $all[$discipline] = [
                'default' => $default,
                'color' => $overrides[$discipline] ?? $default,
                'configured' => isset($overrides[$discipline]),
            ];
        }
        return $all;
    }
}
