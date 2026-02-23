<?php

namespace App\Support;

use App\Models\School;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SiteSettings
{
    /** @var array<string, array<string, mixed>> */
    protected static array $settingsCache = [];

    protected static ?School $resolvedSchool = null;

    protected static bool $schoolResolved = false;

    public static function defaults(): array
    {
        return [
            'school_name' => config('app.name', 'School Portal'),
            'school_location' => 'Awka, Anambra',
            'school_motto' => 'Modern Learning. Strong Values. Real Results.',
            'about_summary' => 'A modern learning community focused on academic excellence, leadership development, and moral values.',
            'mission' => 'To deliver holistic education that develops intellectual ability, emotional strength, ethical discipline, and leadership confidence.',
            'vision' => 'To be a leading institution known for raising globally competent, value-driven, and purpose-led graduates.',
            'school_promise' => 'Every student is seen, supported, and challenged to become the best version of themselves.',
            'contact' => [
                'address' => '13 Chief Mbanefo E. Uduezue Street, Umubele, Awka, Anambra State, Nigeria',
                'phone_primary' => '+234 806 602 5508',
                'phone_secondary' => '+234 803 731 5741',
                'email' => 'info@elitesinternationalcollege.com',
                'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3930.220031261229!2d7.070343014768556!3d6.219634895504751!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10438d77b1d8123d%3A0xc3892dc166d86778!2sUmubele%2C%20Awka%2C%20Anambra!5e0!3m2!1sen!2sng!4v1713892145672!5m2!1sen!2sng',
            ],
            'home' => [
                'hero_badge' => 'Modern Learning. Strong Values. Real Results.',
                'hero_title' => config('app.name', 'School Portal'),
                'hero_highlight' => 'Building Leaders for Tomorrow',
                'hero_description' => 'A complete secondary school experience with excellent teaching, strong discipline, modern technology, and a supportive environment for every child.',
                'contact_title' => 'Book a campus visit and talk with our admissions team',
                'contact_description' => 'We are open for enquiries, tours, and registration support for new families.',
            ],
            'about_page' => [
                'hero_badge' => 'Who We Are',
                'hero_title' => 'About ' . config('app.name', 'Our School'),
                'hero_highlight' => 'Excellence with Character',
                'hero_description' => 'We are a learning community committed to academic excellence, personal discipline, and leadership development. Every learner is guided to think critically, grow confidently, and contribute meaningfully to society.',
                'story_title' => 'A school built for impact',
                'story_description' => 'Our school was established to provide a balanced education where strong academics meet discipline, values, and practical life readiness.',
                'identity_title' => 'Mission, vision, and values',
                'visit_title' => 'Come and experience our learning environment',
                'visit_description' => 'Meet our admissions team, explore our campus, and discover how we support every child’s journey.',
            ],
            'admission_page' => [
                'hero_badge' => 'Admission Portal',
                'hero_title' => 'Student Admission Registration',
                'hero_highlight' => 'Start Your Child\'s Journey',
                'hero_description' => 'Complete the form below to submit an admission request. All registrations are reviewed in the school dashboard, and successful applicants are enrolled directly into the student management system.',
            ],
            'contact_page' => [
                'hero_badge' => 'Contact Portal',
                'hero_title' => 'Contact Our School Team',
                'hero_description' => 'Send us your questions about admission, academics, fees, or support. Your message goes directly to the dashboard so the team can respond quickly.',
            ],
            'gallery_page' => [
                'hero_badge' => 'School Gallery',
                'hero_title' => 'Moments That Define Our Campus',
                'hero_description' => 'Explore school events, classroom activities, sports, leadership experiences, and student milestones.',
            ],
            'footer' => [
                'admissions_badge' => 'Admissions Open',
                'admissions_title' => 'Ready to enroll your child?',
                'admissions_description' => 'Start your admission process today or contact us for guidance.',
                'copyright_suffix' => 'All rights reserved.',
            ],
            'meta' => [
                'description' => 'School portal and services.',
                'keywords' => 'School Portal, Results, Exams, Admissions',
                'author' => config('app.name', 'School Portal'),
                'og_description' => 'School portal for learning, exams, and result management.',
            ],
            // Reserved for additional page-specific keys.
            'pages' => [
                'home' => [],
                'about' => [],
                'admission' => [],
                'contact' => [],
                'gallery' => [],
            ],
        ];
    }

    public static function resolveSchool(?Request $request = null): ?School
    {
        if (self::$schoolResolved) {
            return self::$resolvedSchool;
        }

        $request ??= app()->bound('request') ? request() : null;

        $school = null;

        if ($request) {
            $querySchool = trim((string) ($request->query('school') ?? ''));
            $querySchoolId = trim((string) ($request->query('school_id') ?? ''));

            if ($querySchool !== '') {
                $school = self::findSchoolByIdentifier($querySchool);
            } elseif ($querySchoolId !== '') {
                $school = self::findSchoolByIdentifier($querySchoolId);
            }

            if ($school && $request->hasSession()) {
                $request->session()->put('public_school_id', $school->id);
            }

            if (!$school && $request->hasSession()) {
                $sessionSchoolId = $request->session()->get('public_school_id');
                if ($sessionSchoolId) {
                    $school = School::query()->find((int) $sessionSchoolId);
                }
            }
        }

        if (!$school && Auth::check() && Auth::user()?->school_id) {
            $school = School::query()->find((int) Auth::user()->school_id);
        }

        self::$resolvedSchool = $school;
        self::$schoolResolved = true;

        return self::$resolvedSchool;
    }

    public static function forSchool(?int $schoolId = null): array
    {
        $cacheKey = $schoolId ? 'school:' . $schoolId : 'general';

        if (array_key_exists($cacheKey, self::$settingsCache)) {
            return self::$settingsCache[$cacheKey];
        }

        $settings = self::defaults();

        if (Schema::hasTable('site_settings')) {
            $general = SiteSetting::query()
                ->where('scope_key', SiteSetting::generalScopeKey())
                ->first()?->settings;

            if (is_array($general)) {
                $settings = array_replace_recursive($settings, $general);
            }

            if ($schoolId) {
                $schoolScoped = SiteSetting::query()
                    ->where('scope_key', SiteSetting::schoolScopeKey($schoolId))
                    ->first()?->settings;

                if (is_array($schoolScoped)) {
                    $settings = array_replace_recursive($settings, $schoolScoped);
                }
            }
        }

        if ($schoolId && Schema::hasTable('schools')) {
            $school = School::query()->find($schoolId);
            $settings = self::withSchoolFallbacks($settings, $school);
        }

        self::$settingsCache[$cacheKey] = $settings;

        return $settings;
    }

    public static function clearCache(): void
    {
        self::$settingsCache = [];
        self::$resolvedSchool = null;
        self::$schoolResolved = false;
    }

    protected static function findSchoolByIdentifier(string $identifier): ?School
    {
        if ($identifier === '') {
            return null;
        }

        if (ctype_digit($identifier)) {
            return School::query()->find((int) $identifier);
        }

        return School::query()->where('code', $identifier)->first();
    }

    protected static function withSchoolFallbacks(array $settings, ?School $school): array
    {
        if (!$school) {
            return $settings;
        }

        if (($settings['school_name'] ?? '') === '') {
            $settings['school_name'] = (string) $school->name;
        }

        if (($settings['contact']['address'] ?? '') === '' && $school->address) {
            $settings['contact']['address'] = (string) $school->address;
        }

        if (($settings['contact']['phone_primary'] ?? '') === '' && $school->phone) {
            $settings['contact']['phone_primary'] = (string) $school->phone;
        }

        if (($settings['contact']['email'] ?? '') === '' && $school->email) {
            $settings['contact']['email'] = (string) $school->email;
        }

        if (($settings['home']['hero_title'] ?? '') === '') {
            $settings['home']['hero_title'] = (string) $school->name;
        }

        if (($settings['about_page']['hero_title'] ?? '') === '') {
            $settings['about_page']['hero_title'] = 'About ' . $school->name;
        }

        $settings['meta']['author'] = (string) ($settings['meta']['author'] ?? $school->name);

        return $settings;
    }
}
