<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Queue;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Tests\Extend\JobExtend;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class TenantAwareJobTest extends Test
{
    /**
     * @var Environment
     */
    protected $environment;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->environment = $app->make(Environment::class);
    }

    /**
     * @test
     */
    public function serializes_tenant()
    {
        $this->mockHttpRequest();

        $this->app->make(CurrentHostname::class);

        $job = new JobExtend();

        $attributes = serialize($job);

        // switch to other
        $this->environment->hostname($this->tenant);

        /** @var CurrentHostname $identified */
        $identified = $this->app->make(CurrentHostname::class);

        /** @var JobExtend $job */
        $job = unserialize($attributes);

        $this->assertInstanceOf(JobExtend::class, $job);

        $restored = $this->environment->hostname();

        $this->assertNotEquals($identified->fqdn, $restored->fqdn);
    }
}
