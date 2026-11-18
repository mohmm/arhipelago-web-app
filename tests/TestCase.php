<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The localization redirect middleware rewrites locale-less URLs to a
        // locale-prefixed form, turning ordinary test requests into 302
        // redirects. Disable only the redirect filters so feature tests can
        // assert against the real controller response; locale resolution for
        // translations still happens via the remaining localization middleware.
        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
        ]);
    }
}
