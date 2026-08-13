<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        $this->guardTestDatabase();

        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Refuse to run against anything that does not look like a dedicated test
     * database — RefreshDatabase wipes the connected schema. Runs before the app
     * boots (and before RefreshDatabase migrates), reading the raw environment.
     */
    private function guardTestDatabase(): void
    {
        $database = (string) ($_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');

        if ($database !== '' && $database !== ':memory:' && ! str_contains(strtolower($database), 'test')) {
            throw new \RuntimeException(
                "Refusing to run the test suite: DB_DATABASE '{$database}' is not a test database "
                . "(its name must contain 'test'). Point tests at a dedicated test database.",
            );
        }
    }
}
