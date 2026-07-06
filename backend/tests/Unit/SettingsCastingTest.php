<?php

namespace Tests\Unit;

use App\Support\Settings;
use Tests\TestCase;

/**
 * Development Roadmap, Milestone 1 Testing section: "Unit: settings
 * key/value casting (boolean/int/string)."
 */
class SettingsCastingTest extends TestCase
{
    public function test_casts_string_values(): void
    {
        $this->assertSame('PN Knowledge Campus', Settings::cast('campus_name', 'PN Knowledge Campus'));
    }

    public function test_casts_int_values(): void
    {
        $this->assertSame(42, Settings::cast('logo_media_id', '42'));
    }

    public function test_casts_null_values_regardless_of_type(): void
    {
        $this->assertNull(Settings::cast('campus_name', null));
        $this->assertNull(Settings::cast('logo_media_id', null));
    }

    public function test_is_valid_key_and_is_public_reflect_the_registry(): void
    {
        $this->assertTrue(Settings::isValidKey('campus_name'));
        $this->assertFalse(Settings::isValidKey('not_a_real_key'));

        $this->assertTrue(Settings::isPublic('campus_name'));
        $this->assertFalse(Settings::isPublic('smtp_password'));
    }

    public function test_group_for_returns_the_declared_group(): void
    {
        $this->assertSame('campus', Settings::groupFor('campus_name'));
        $this->assertSame('smtp', Settings::groupFor('smtp_host'));
        $this->assertNull(Settings::groupFor('not_a_real_key'));
    }

    /**
     * Settings-module extension: Short Name, Address, Google Maps, and
     * expanded SEO Defaults keys added alongside Office Hours.
     */
    public function test_new_keys_are_registered_with_the_expected_group_and_visibility(): void
    {
        $this->assertSame('campus', Settings::groupFor('campus_short_name'));
        $this->assertSame('contact', Settings::groupFor('contact_address'));
        $this->assertSame('maps', Settings::groupFor('google_maps_embed_url'));
        $this->assertSame('seo_defaults', Settings::groupFor('default_og_image_media_id'));

        $this->assertTrue(Settings::isPublic('google_maps_api_key'));
        $this->assertSame(7, Settings::cast('default_og_image_media_id', '7'));
    }
}
