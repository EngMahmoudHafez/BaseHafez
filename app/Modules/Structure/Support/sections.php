<?php

/**
 * Declarative section registry for the Structure module.
 *
 * This is the SINGLE source of truth for every editable content section. Every
 * section is a generic content "page": adding one is a single entry in the
 * `sections` array below — no controller, request, service, or view is required.
 * {@see SectionRegistry} loads this file; the generic dashboard editor,
 * validation, seeding and public serialization all read from it.
 *
 * Per-section keys:
 *   label       translation key for the dashboard heading
 *   translated  true when content is stored per-locale (ar/en)
 *   public      true when the content is exposed through GET /web/v1/structures/{key}
 *   shared      non-localized fields (posted under `all[...]`, copied into both locales)
 *   fields      localized flat fields (posted under `ar[...]` / `en[...]`)
 *   groups      localized named field groups (e.g. about → vision/mission)
 *   repeatables named add/remove lists; each carries `fields` and optional `item` meta
 *               (any of: key, sort_order, visible, rating) and `shared` when non-localized
 *   defaults    optional seed content keyed by locale
 *
 * A field definition is: ['type' => text|textarea|url|number|checkbox|image,
 *                         'label' => <lang key>, 'rules' => [...], 'col' => <bootstrap col>,
 *                         'file_id' => <stable id, image fields only>].
 */

use App\Modules\Structure\Support\SectionRegistry;

$policyBlocks = [
    'label' => 'dashboard.Cards',
    'max' => SectionRegistry::MAX_ITEMS,
    'fields' => [
        'title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['required', 'string']],
    ],
];

$policyContent = fn (string $title, string $description): array => [
    'main_title' => $title,
    'desc1' => $description,
    'blocks' => [],
];

$policySection = fn (string $label, bool $public, string $arTitle, string $enTitle, string $arDescription, string $enDescription): array => [
    'label' => $label,
    'translated' => true,
    'public' => $public,
    'fields' => [
        'main_title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-6'],
        'desc1' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ],
    'repeatables' => [
        'blocks' => $policyBlocks,
    ],
    'defaults' => [
        'ar' => $policyContent($arTitle, $arDescription),
        'en' => $policyContent($enTitle, $enDescription),
    ],
];

$aboutSection = fn (string $title): array => ['title' => $title, 'description' => '', 'image' => null];

return [
    'locales' => ['ar', 'en'],

    'sections' => [

        'hero' => [
            'label' => 'dashboard.Hero Section',
            'translated' => true,
            'public' => true,
            'fields' => [
                'slogan' => ['type' => 'text', 'label' => 'dashboard.homepage_fields.eyebrow', 'rules' => ['nullable', 'string', 'max:255']],
                'title_primary' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:500']],
                'title_highlight' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['nullable', 'string', 'max:255']],
                'title_accent' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['nullable', 'string', 'max:255']],
                'subtitle' => ['type' => 'textarea', 'label' => 'dashboard.homepage_fields.subtitle', 'rules' => ['required', 'string'], 'col' => 'col-12'],
                'button_text' => ['type' => 'text', 'label' => 'dashboard.homepage_fields.button_label', 'rules' => ['nullable', 'string', 'max:255']],
                'button_link' => ['type' => 'url', 'label' => 'dashboard.homepage_fields.button_target', 'rules' => ['nullable', 'url', 'max:500']],
                'hero_image' => ['type' => 'image', 'label' => 'dashboard.Hero Image', 'file_id' => 'hero_image', 'col' => 'col-12'],
            ],
            'defaults' => [
                'ar' => [
                    'slogan' => 'ابدأ مشروعك بثقة',
                    'title_primary' => 'أساس مرن لمشروعك القادم',
                    'title_highlight' => '',
                    'title_accent' => '',
                    'subtitle' => 'خصّص هذا المحتوى من لوحة التحكم ليتوافق مع هوية مشروعك.',
                    'button_text' => 'تواصل معنا',
                    'button_link' => null,
                    'hero_image' => null,
                ],
                'en' => [
                    'slogan' => 'Start with confidence',
                    'title_primary' => 'A flexible foundation for your next project',
                    'title_highlight' => '',
                    'title_accent' => '',
                    'subtitle' => 'Customize this content from the dashboard to match your project identity.',
                    'button_text' => 'Contact us',
                    'button_link' => null,
                    'hero_image' => null,
                ],
            ],
        ],

        'about' => [
            'label' => 'dashboard.About Us Section',
            'translated' => true,
            'public' => false,
            'groups' => [
                'about_us' => ['label' => 'dashboard.about_section_about', 'fields' => [
                    'title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255']],
                    'description' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['required', 'string'], 'col' => 'col-12'],
                    'image' => ['type' => 'image', 'label' => 'dashboard.image', 'file_id' => 600, 'col' => 'col-12'],
                ]],
                'vision' => ['label' => 'dashboard.about_section_vision', 'fields' => [
                    'title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255']],
                    'description' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['required', 'string'], 'col' => 'col-12'],
                    'image' => ['type' => 'image', 'label' => 'dashboard.image', 'file_id' => 601, 'col' => 'col-12'],
                ]],
                'mission' => ['label' => 'dashboard.about_section_mission', 'fields' => [
                    'title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255']],
                    'description' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['required', 'string'], 'col' => 'col-12'],
                    'image' => ['type' => 'image', 'label' => 'dashboard.image', 'file_id' => 602, 'col' => 'col-12'],
                ]],
                'goals' => ['label' => 'dashboard.about_section_goals', 'fields' => [
                    'title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255']],
                    'description' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['required', 'string'], 'col' => 'col-12'],
                    'image' => ['type' => 'image', 'label' => 'dashboard.image', 'file_id' => 603, 'col' => 'col-12'],
                ]],
            ],
            'defaults' => [
                'ar' => [
                    'about_us' => $aboutSection('من نحن'),
                    'vision' => $aboutSection('رؤيتنا'),
                    'mission' => $aboutSection('رسالتنا'),
                    'goals' => $aboutSection('أهدافنا'),
                ],
                'en' => [
                    'about_us' => $aboutSection('About us'),
                    'vision' => $aboutSection('Our vision'),
                    'mission' => $aboutSection('Our mission'),
                    'goals' => $aboutSection('Our goals'),
                ],
            ],
        ],

        'why_choose_us' => [
            'label' => 'dashboard.Why Choose Us',
            'translated' => true,
            'public' => false,
            'fields' => [
                'subtitle' => ['type' => 'text', 'label' => 'dashboard.homepage_fields.subtitle', 'rules' => ['nullable', 'string', 'max:255']],
                'title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:500'], 'col' => 'col-12'],
            ],
            'repeatables' => [
                'cards' => [
                    'label' => 'dashboard.Cards',
                    'max' => SectionRegistry::MAX_ITEMS,
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255']],
                        'description' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
                    ],
                ],
            ],
        ],

        'contact' => [
            'label' => 'dashboard.Contact Section',
            'translated' => true,
            'public' => true,
            'fields' => [
                'form_title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255']],
                'form_subtitle' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['required', 'string'], 'col' => 'col-12'],
            ],
            'shared' => [
                'phone' => ['type' => 'text', 'label' => 'dashboard.Phone Number', 'rules' => ['required', 'string', 'max:255']],
                'whatsapp' => ['type' => 'text', 'label' => 'dashboard.WhatsApp Number', 'rules' => ['required', 'string', 'max:255']],
            ],
            'defaults' => [
                'ar' => ['form_title' => 'تواصل معنا', 'form_subtitle' => 'أضف بيانات التواصل الخاصة بمشروعك من لوحة التحكم.', 'phone' => '', 'whatsapp' => ''],
                'en' => ['form_title' => 'Contact us', 'form_subtitle' => 'Add your project contact details from the dashboard.', 'phone' => '', 'whatsapp' => ''],
            ],
        ],

        'footer' => [
            'label' => 'dashboard.footer',
            'translated' => true,
            'public' => true,
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'dashboard.title', 'rules' => ['required', 'string', 'max:255']],
                'subtitle' => ['type' => 'text', 'label' => 'dashboard.homepage_fields.subtitle', 'rules' => ['required', 'string', 'max:255']],
                'desc' => ['type' => 'textarea', 'label' => 'dashboard.description', 'rules' => ['required', 'string'], 'col' => 'col-12'],
            ],
            'shared' => [
                'logo' => ['type' => 'image', 'label' => 'dashboard.Logo', 'file_id' => 900, 'col' => 'col-12'],
                'phone' => ['type' => 'text', 'label' => 'dashboard.Phone Number', 'rules' => ['nullable', 'string', 'max:50']],
                'whatsapp' => ['type' => 'text', 'label' => 'dashboard.WhatsApp Number', 'rules' => ['nullable', 'string', 'max:50']],
                'email' => ['type' => 'text', 'label' => 'dashboard.Email Address', 'rules' => ['nullable', 'email', 'max:255']],
            ],
            'repeatables' => [
                'social_links' => [
                    'label' => 'dashboard.Social Icon',
                    'shared' => true,
                    'max' => SectionRegistry::MAX_ITEMS,
                    'fields' => [
                        'url' => ['type' => 'url', 'label' => 'dashboard.homepage_fields.button_target', 'rules' => ['required', 'url', 'max:500']],
                        'icon' => ['type' => 'text', 'label' => 'dashboard.Icon', 'rules' => ['nullable', 'string', 'max:255']],
                    ],
                ],
            ],
            'defaults' => [
                'ar' => ['title' => 'اسم المشروع', 'subtitle' => 'نبذة مختصرة', 'desc' => 'خصّص بيانات التذييل من لوحة التحكم.', 'logo' => null, 'phone' => '', 'whatsapp' => '', 'email' => '', 'social_links' => []],
                'en' => ['title' => 'Project name', 'subtitle' => 'Short introduction', 'desc' => 'Customize the footer details from the dashboard.', 'logo' => null, 'phone' => '', 'whatsapp' => '', 'email' => '', 'social_links' => []],
            ],
        ],

        'terms_and_conditions' => $policySection(
            'dashboard.terms_and_conditions',
            true,
            'الشروط والأحكام',
            'Terms and Conditions',
            'أضف الشروط والأحكام المعتمدة لمشروعك هنا.',
            'Add your approved terms and conditions here.',
        ),

        'privacy_policy' => $policySection(
            'dashboard.privacy_policy',
            false,
            'سياسة الخصوصية',
            'Privacy Policy',
            'أضف سياسة الخصوصية المعتمدة لمشروعك هنا.',
            'Add your approved privacy policy here.',
        ),
    ],
];
