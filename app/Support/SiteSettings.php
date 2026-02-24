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
            'theme' => [
                'primary_color' => '#dc2626',
                'logo_url' => '',
                'favicon_url' => '',
            ],
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
                'story_extra' => 'We combine structured teaching, co-curricular exposure, and close mentoring to help students discover their strengths and pursue clear goals.',
                'story_quote' => 'Our focus is not just high scores, but complete growth: mind, character, and leadership.',
                'identity_title' => 'Mission, vision, and values',
                'pillars_title' => 'How we build student success',
                'leadership_title' => 'Teams that drive quality education',
                'stats_title' => 'Our impact at a glance',
                'faq_title' => 'What parents and students ask',
                'faq_subtitle' => 'Frequently Asked Questions',
                'updates_title' => 'Updates and Calendar',
                'updates_subtitle' => 'Upcoming events and school calendar',
                'student_voice_title' => 'Student Voice',
                'student_voice_subtitle' => 'What students and families are saying',
                'visit_title' => 'Come and experience our learning environment',
                'visit_description' => 'Meet our admissions team, explore our campus, and discover how we support every child’s journey.',
                'milestones' => [
                    [
                        'title' => 'Foundation Stage',
                        'note' => 'Built with a mission to deliver strong academics and character development.',
                        'dotClass' => 'bg-red-500',
                    ],
                    [
                        'title' => 'Growth Stage',
                        'note' => 'Expanded student support systems, clubs, and practical learning structure.',
                        'dotClass' => 'bg-orange-500',
                    ],
                    [
                        'title' => 'Digital Learning Stage',
                        'note' => 'Integrated technology-driven learning and data-backed performance tracking.',
                        'dotClass' => 'bg-blue-500',
                    ],
                    [
                        'title' => 'Future Forward',
                        'note' => 'Strengthening innovation, leadership culture, and global readiness.',
                        'dotClass' => 'bg-violet-500',
                    ],
                ],
                'values' => [
                    [
                        'title' => 'Excellence',
                        'text' => 'We pursue high standards in teaching, learning, and outcomes.',
                        'icon' => 'fa-medal',
                        'cardClass' => 'border-red-200 bg-red-50 text-red-900',
                        'badgeClass' => 'bg-red-600 text-white',
                    ],
                    [
                        'title' => 'Integrity',
                        'text' => 'We build honesty, accountability, and trust in daily school life.',
                        'icon' => 'fa-shield-halved',
                        'cardClass' => 'border-orange-200 bg-orange-50 text-orange-900',
                        'badgeClass' => 'bg-orange-600 text-white',
                    ],
                    [
                        'title' => 'Leadership',
                        'text' => 'Students are encouraged to take initiative and lead with confidence.',
                        'icon' => 'fa-flag',
                        'cardClass' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'badgeClass' => 'bg-amber-600 text-white',
                    ],
                    [
                        'title' => 'Innovation',
                        'text' => 'Creative thinking and practical problem-solving are part of learning.',
                        'icon' => 'fa-lightbulb',
                        'cardClass' => 'border-cyan-200 bg-cyan-50 text-cyan-900',
                        'badgeClass' => 'bg-cyan-600 text-white',
                    ],
                    [
                        'title' => 'Discipline',
                        'text' => 'Structure and consistency shape student habits and responsibility.',
                        'icon' => 'fa-list-check',
                        'cardClass' => 'border-indigo-200 bg-indigo-50 text-indigo-900',
                        'badgeClass' => 'bg-indigo-600 text-white',
                    ],
                    [
                        'title' => 'Service',
                        'text' => 'We inspire students to contribute positively to the community.',
                        'icon' => 'fa-hand-holding-heart',
                        'cardClass' => 'border-rose-200 bg-rose-50 text-rose-900',
                        'badgeClass' => 'bg-rose-600 text-white',
                    ],
                ],
                'pillar_tabs' => [
                    [
                        'key' => 'academics',
                        'label' => 'Academic Rigor',
                    ],
                    [
                        'key' => 'mentorship',
                        'label' => 'Mentorship',
                    ],
                    [
                        'key' => 'coCurricular',
                        'label' => 'Co-Curricular',
                    ],
                    [
                        'key' => 'technology',
                        'label' => 'Technology',
                    ],
                ],
                'pillars' => [
                    'academics' => [
                        'title' => 'Academic Rigor and Structured Progress',
                        'description' => 'A clear curriculum plan with continuous assessments and targeted support for steady improvement.',
                        'points' => [
                            'Focused teaching plans by level and subject',
                            'Regular performance reviews and interventions',
                            'Exam readiness culture with guided preparation',
                        ],
                        'outcomes' => ['Stronger foundation', 'Higher consistency', 'Clear progress tracking'],
                        'boxClass' => 'border-blue-200 bg-blue-50 text-blue-900',
                        'labelClass' => 'text-blue-700',
                    ],
                    'mentorship' => [
                        'title' => 'Mentorship and Character Formation',
                        'description' => 'Every student receives guidance on academics, discipline, values, and personal growth.',
                        'points' => [
                            'Class-based mentoring and counseling support',
                            'Leadership coaching and confidence-building',
                            'Positive behavior and responsibility culture',
                        ],
                        'outcomes' => ['Better focus', 'Strong values', 'Confidence growth'],
                        'boxClass' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                        'labelClass' => 'text-emerald-700',
                    ],
                    'coCurricular' => [
                        'title' => 'Co-Curricular Development',
                        'description' => 'Balanced education through sports, clubs, competitions, and creative platforms.',
                        'points' => [
                            'Clubs for debate, science, arts, and writing',
                            'Sports programs and team participation',
                            'Public speaking and collaboration activities',
                        ],
                        'outcomes' => ['Teamwork', 'Creativity', 'Leadership exposure'],
                        'boxClass' => 'border-purple-200 bg-purple-50 text-purple-900',
                        'labelClass' => 'text-purple-700',
                    ],
                    'technology' => [
                        'title' => 'Technology-Enabled Learning',
                        'description' => 'Digital tools improve teaching quality, tracking, communication, and learning access.',
                        'points' => [
                            'Technology-supported classroom delivery',
                            'Data-informed student performance review',
                            'Digital communication with parents and guardians',
                        ],
                        'outcomes' => ['Faster feedback', 'Better visibility', 'Modern readiness'],
                        'boxClass' => 'border-cyan-200 bg-cyan-50 text-cyan-900',
                        'labelClass' => 'text-cyan-700',
                    ],
                ],
                'leadership' => [
                    [
                        'role' => 'Principal',
                        'unit' => 'School Leadership Office',
                        'note' => 'Provides direction, quality assurance, and strategic school development.',
                        'icon' => 'fa-user-tie',
                        'iconClass' => 'bg-red-600',
                        'roleClass' => 'text-red-700',
                    ],
                    [
                        'role' => 'Academic Team',
                        'unit' => 'Teaching & Curriculum',
                        'note' => 'Drives curriculum delivery, lesson quality, and exam readiness.',
                        'icon' => 'fa-book-open-reader',
                        'iconClass' => 'bg-blue-600',
                        'roleClass' => 'text-blue-700',
                    ],
                    [
                        'role' => 'Student Affairs',
                        'unit' => 'Welfare & Guidance',
                        'note' => 'Supports behavior, counseling, and student wellbeing.',
                        'icon' => 'fa-user-group',
                        'iconClass' => 'bg-teal-600',
                        'roleClass' => 'text-teal-700',
                    ],
                    [
                        'role' => 'Operations Team',
                        'unit' => 'School Administration',
                        'note' => 'Ensures smooth campus operations and learning support systems.',
                        'icon' => 'fa-gear',
                        'iconClass' => 'bg-violet-600',
                        'roleClass' => 'text-violet-700',
                    ],
                ],
                'stats' => [
                    [
                        'label' => 'Students Mentored',
                        'target' => 1200,
                        'suffix' => '+',
                        'cardClass' => 'border-red-200 bg-red-50',
                        'valueClass' => 'text-red-700',
                    ],
                    [
                        'label' => 'Certified Teachers',
                        'target' => 85,
                        'suffix' => '+',
                        'cardClass' => 'border-orange-200 bg-orange-50',
                        'valueClass' => 'text-orange-700',
                    ],
                    [
                        'label' => 'Learning Programs',
                        'target' => 30,
                        'suffix' => '+',
                        'cardClass' => 'border-blue-200 bg-blue-50',
                        'valueClass' => 'text-blue-700',
                    ],
                    [
                        'label' => 'Success Rate',
                        'target' => 98,
                        'suffix' => '%',
                        'cardClass' => 'border-violet-200 bg-violet-50',
                        'valueClass' => 'text-violet-700',
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'What makes Elites International College different?',
                        'a' => 'We combine strong academics, disciplined culture, mentoring support, and practical student development.',
                    ],
                    [
                        'q' => 'How does the school support student growth?',
                        'a' => 'Through structured teaching, regular assessments, counseling support, and co-curricular opportunities.',
                    ],
                    [
                        'q' => 'Can parents stay informed about student progress?',
                        'a' => 'Yes. The school provides regular communication and progress visibility through reports and portal tools.',
                    ],
                    [
                        'q' => 'How can I learn more about admissions?',
                        'a' => 'Visit the Admission page or Contact page to speak with the admissions team.',
                    ],
                ],
                'updates' => [
                    [
                        'title' => 'Inter-House Sports Festival',
                        'date' => 'March 20, 2026',
                        'note' => 'Campus-wide sports and cultural showcase for all houses.',
                    ],
                    [
                        'title' => 'Science and Innovation Week',
                        'date' => 'April 8, 2026',
                        'note' => 'Student projects, lab demos, and parent exhibition day.',
                    ],
                    [
                        'title' => 'Mid-Term Progress Briefing',
                        'date' => 'May 2, 2026',
                        'note' => 'Parents and guardians meet mentors for performance review.',
                    ],
                ],
                'calendar' => [
                    [
                        'period' => 'March 2026',
                        'activity' => 'Continuous Assessment 2',
                    ],
                    [
                        'period' => 'April 2026',
                        'activity' => 'Holiday Coaching and Club Clinics',
                    ],
                    [
                        'period' => 'May 2026',
                        'activity' => 'Second Term Examinations',
                    ],
                ],
                'student_voice' => [
                    [
                        'name' => 'Amara E.',
                        'role' => 'SS2 Student',
                        'quote' => 'The mentorship system helped me become more confident in class and in leadership roles.',
                    ],
                    [
                        'name' => 'Daniel O.',
                        'role' => 'Parent',
                        'quote' => 'We get timely updates, and the school genuinely partners with families for student progress.',
                    ],
                    [
                        'name' => 'Chioma N.',
                        'role' => 'JSS3 Student',
                        'quote' => 'Clubs and projects make learning practical, exciting, and useful for real life.',
                    ],
                ],
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
                'social' => [
                    'facebook' => '',
                    'instagram' => '',
                    'x' => '',
                    'whatsapp' => '',
                ],
            ],
            'meta' => [
                'description' => 'School portal and services.',
                'keywords' => 'School Portal, Results, Exams, Admissions',
                'author' => config('app.name', 'School Portal'),
                'og_description' => 'School portal for learning, exams, and result management.',
            ],
            'seo' => [
                'home' => [
                    'meta_title' => '',
                    'meta_description' => '',
                    'social_image_url' => '',
                ],
                'about' => [
                    'meta_title' => '',
                    'meta_description' => '',
                    'social_image_url' => '',
                ],
                'admission' => [
                    'meta_title' => '',
                    'meta_description' => '',
                    'social_image_url' => '',
                ],
                'contact' => [
                    'meta_title' => '',
                    'meta_description' => '',
                    'social_image_url' => '',
                ],
                'gallery' => [
                    'meta_title' => '',
                    'meta_description' => '',
                    'social_image_url' => '',
                ],
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

        if (($settings['theme']['logo_url'] ?? '') === '' && !empty($school->logo_url)) {
            $settings['theme']['logo_url'] = (string) $school->logo_url;
        }

        if (($settings['meta']['author'] ?? '') === '') {
            $settings['meta']['author'] = (string) $school->name;
        }

        return $settings;
    }
}
