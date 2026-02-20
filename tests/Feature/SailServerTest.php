<?php

namespace Tests\Feature;

use Tests\TestCase;

class SailServerTest extends TestCase
{
    public function test_the_homepage()
    {
        $this->get('/')->assertSee('Laravel Sailor');
    }

    public function test_it_can_return_the_sail_install_script()
    {
        $response = $this->get('/example-app');

        $response->assertStatus(200);
        $response->assertSee("laravelsail/php84-composer:latest");
        $response->assertSee('bash -c "composer global require laravel/installer && rm /usr/bin/laravel && ln -s ~/.composer/vendor/bin/laravel /usr/bin/laravel && laravel new example-app --pest --no-interaction && cd example-app && php ./artisan sail:install --with=mysql,redis,meilisearch,mailpit,selenium "', false);
    }

    public function test_different_php_versions_can_be_picked()
    {
        $response = $this->get('/example-app?php=80');

        $response->assertStatus(200);
        $response->assertSee("laravelsail/php80-composer:latest");
    }

    public function test_different_services_can_be_picked()
    {
        $response = $this->get('/example-app?with=pgsql,redis,selenium');

        $response->assertStatus(200);
        $response->assertSee('php ./artisan sail:install --with=pgsql,redis,selenium');
    }

    public function test_no_services_can_be_picked()
    {
        $response = $this->get('/example-app?with=none');

        $response->assertStatus(200);
        $response->assertSee('php ./artisan sail:install --with=none');
    }

    public function test_it_removes_duplicated_valid_services()
    {
        $response = $this->get('/example-app?with=redis,redis');

        $response->assertStatus(200);
        $response->assertSee('bash -c "composer global require laravel/installer && rm /usr/bin/laravel && ln -s ~/.composer/vendor/bin/laravel /usr/bin/laravel && laravel new example-app --pest --no-interaction && cd example-app && php ./artisan sail:install --with=redis "', false);
    }

    public function test_it_adds_the_devcontainer_upon_request()
    {
        $response = $this->get('/example-app?with=pgsql&devcontainer');

        $response->assertStatus(200);
        $response->assertSee('bash -c "composer global require laravel/installer && rm /usr/bin/laravel && ln -s ~/.composer/vendor/bin/laravel /usr/bin/laravel && laravel new example-app --pest --no-interaction && cd example-app && php ./artisan sail:install --with=pgsql --devcontainer"', false);
    }

    public function test_it_does_not_accepts_domains_with_a_dot()
    {
        $response = $this->get('/foo.test');

        $response->assertStatus(400);
        $response->assertSee('Invalid site name. Please only use alpha-numeric characters, dashes, and underscores.');
    }

    // public function test_it_does_not_accept_empty_php_query_if_present()
    // {
    //     $response = $this->get('/example-app?php');

    //     $response->assertStatus(400);
    //     $response->assertSee('Invalid PHP version. Please specify a supported version (74, 80, 81, 82, 83, or 84).');
    // }

    public function test_it_does_accept_empty_php_query_and_uses_php_84()
    {
        $response = $this->get('/example-app?php');

        $response->assertStatus(200);
        $response->assertSee('laravelsail/php84-composer:latest');
    }

    public function test_it_does_not_accept_invalid_php_versions()
    {
        $response = $this->get('/example-app?php=1000');

        $response->assertStatus(400);
        $response->assertSee('Invalid PHP version. Please specify a supported version (74, 80, 81, 82, 83, or 84).');
    }

    // public function test_it_does_not_accept_empty_with_query_when_present()
    // {
    //     $response = $this->get('/example-app?with');

    //     $response->assertStatus(400);
    //     $response->assertSee('Invalid service name. Please provide one or more of the supported services (mysql, pgsql, mariadb, mongodb, redis, rabbitmq, valkey, memcached, meilisearch, typesense, minio, mailpit, selenium, soketi) or "none".', false);
    // }

    public function test_it_does_accept_empty_with_query_and_uses_default_services()
    {
        $response = $this->get('/example-app?with');

        $response->assertStatus(200);
        $response->assertSee('php ./artisan sail:install --with=mysql,redis,meilisearch,mailpit,selenium');
    }

    public function test_it_does_not_accept_invalid_services()
    {
        $response = $this->get('/example-app?with=redis,invalid_service_name');

        $response->assertStatus(400);
        $response->assertSee('Invalid service name. Please provide one or more of the supported services (mysql, pgsql, mariadb, mongodb, redis, rabbitmq, valkey, memcached, meilisearch, typesense, minio, mailpit, selenium, soketi) or "none".', false);
    }

    public function test_it_does_not_accept_none_with_other_services()
    {
        $response = $this->get('/example-app?with=none,redis');

        $response->assertStatus(400);
        $response->assertSee('Invalid service name. Please provide one or more of the supported services (mysql, pgsql, mariadb, mongodb, redis, rabbitmq, valkey, memcached, meilisearch, typesense, minio, mailpit, selenium, soketi) or "none".', false);
    }

    public function test_livewire_frontend_can_be_picked()
    {
        $response = $this->get('/example-app?frontend=livewire');

        $response->assertStatus(200);
        $response->assertSee('--livewire');
    }

    public function test_livewire_class_components_frontend_can_be_picked()
    {
        $response = $this->get('/example-app?frontend=livewire-class-components');

        $response->assertStatus(200);
        $response->assertSee('--livewire-class-components');
    }

    public function test_react_frontend_can_be_picked()
    {
        $response = $this->get('/example-app?frontend=react');

        $response->assertStatus(200);
        $response->assertSee('--react');
    }

    public function test_vue_frontend_can_be_picked()
    {
        $response = $this->get('/example-app?frontend=vue');

        $response->assertStatus(200);
        $response->assertSee('--vue');
    }

    public function test_svelte_frontend_can_be_picked()
    {
        $response = $this->get('/example-app?frontend=svelte');

        $response->assertStatus(200);
        $response->assertSee('--svelte');
    }

    public function test_different_javascript_runtimes_can_be_picked()
    {
        $runtimes = ['npm', 'pnpm', 'bun', 'yarn'];

        foreach ($runtimes as $runtime) {
            $response = $this->get("/example-app?javascript={$runtime}");

            $response->assertStatus(200);
            $response->assertSee("--{$runtime}");
        }
    }

    public function test_it_does_not_accept_invalid_javascript_runtimes()
    {
        $response = $this->get('/example-app?javascript=invalid_runtime');

        $response->assertStatus(400);
        $response->assertSee('Invalid JavaScript runtime. Please provide one supported runtime (npm, pnpm, bun, yarn) or leave it empty.', false);
    }

    public function test_boost_flag_can_be_added()
    {
        $response = $this->get('/example-app?boost');

        $response->assertStatus(200);
        $response->assertSee('--boost');
    }

    public function test_boost_flag_works_with_other_options()
    {
        $response = $this->get('/example-app?with=redis&frontend=vue&boost');

        $response->assertStatus(200);
        $response->assertSee('--boost');
        $response->assertSee('--vue');
        $response->assertSee('--with=redis');
    }

    public function test_javascript_runtime_works_with_other_options()
    {
        $response = $this->get('/example-app?javascript=pnpm&frontend=react&with=redis');

        $response->assertStatus(200);
        $response->assertSee('--pnpm');
        $response->assertSee('--react');
        $response->assertSee('--with=redis');
    }

    public function test_no_authentication_can_be_picked()
    {
        $response = $this->get('/example-app?auth=no-authentication');

        $response->assertStatus(200);
        $response->assertSee('--no-authentication');
    }

    public function test_all_new_options_work_together()
    {
        $response = $this->get('/example-app?with=pgsql,redis&frontend=svelte&javascript=bun&boost&auth=no-authentication');

        $response->assertStatus(200);
        $response->assertSee('--svelte');
        $response->assertSee('--bun');
        $response->assertSee('--boost');
        $response->assertSee('--no-authentication');
        $response->assertSee('php ./artisan sail:install --with=pgsql,redis');
    }
}
