<?php

namespace Mtvtd\LaravelStats\Tests\ShareableMetrics;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\LaravelAbout;

class LaravelAboutTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		/* Force the in-memory array cache so the test doesn't depend on
		 * a `cache` table existing in Testbench's SQLite DB. */
		config()->set('cache.default', 'array');
	}

	public function test_metric_name_is_about()
	{
		$this->assertSame('about', (new LaravelAbout())->name());
	}

	public function test_value_is_cached_under_the_lstats_about_key()
	{
		Cache::forget('LSTATS::ABOUT:APPLICATION');

		(new LaravelAbout())->value();

		$this->assertTrue(Cache::has('LSTATS::ABOUT:APPLICATION'));
	}

	public function test_value_returns_an_empty_array_when_collection_throws()
	{
		Cache::put('LSTATS::ABOUT:APPLICATION', '{not valid json', now()->addHour());

		$this->assertSame([], (new LaravelAbout())->value());
	}

	public function test_format_information_normalises_ints_blanks_and_passes_through_bool_and_array()
	{
		$input = new Collection([
			'Section' => [
				'count' => 1234,
				'blank_string' => '',
				'live_string' => 'value',
				'flag' => true,
				'nested' => ['a', 'b'],
				'other' => 3.14,
			],
		]);

		$out = (new LaravelAbout())->formatInformation($input)->toArray();

		$this->assertSame(number_format(1234), $out['Section']['count']);
		$this->assertSame('-', $out['Section']['blank_string']);
		$this->assertSame('value', $out['Section']['live_string']);
		$this->assertTrue($out['Section']['flag']);
		$this->assertSame(['a', 'b'], $out['Section']['nested']);
		$this->assertSame('3.14', $out['Section']['other']);
	}
}
