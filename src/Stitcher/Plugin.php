<?php

namespace Stitcher;

interface Plugin
{
    public static function getConfigurationPath(): ?string;

    public static function getServicesPath(): ?string;

    public static function boot(): void;
}
