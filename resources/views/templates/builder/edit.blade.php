<!doctype html>
<html>
<head>
    <title>{{ trans('messages.edit_template') }} - {{ $template->name }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    @include('layouts._favicon')

    <link href="{{ URL::asset('builder/builder.css') }}" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="{{ URL::asset('builder/builder.js') }}"></script>
    <style>
        @font-face {
            font-family: 'FuturaPT';
            src: local('FuturaPT'), url(/assets2/css/fonts/FuturaPTHeavy.otf) format('truetype');
            font-weight: 500;
            font-style: normal;

        }

        @font-face {
            font-family: 'FuturaPT';
            src: local('FuturaPT'), url(/assets2/css/fonts/FuturaPTBold.otf) format('truetype');
            font-weight: 600;
            font-style: normal;

        }

        /*@font-face {*/
        /*    font-family: 'FuturaPT';*/
        /*    src: local('FuturaPT'), url(Assets/fonts/FuturaPTLight.otf) format('truetype');*/
        /*    font-weight: 400;*/
        /*    font-style: normal;*/
        /*}*/

        @font-face {
            font-family: 'FuturaPT';
            src: local('FuturaPT'), url(/assets2/css/fonts/FuturaPTBook.otf) format('truetype');
            font-weight: 400;
            font-style: normal;

        }

        @font-face {
            font-family: 'FuturaPT';
            src: local('FuturaPT'), url(/assets2/css/fonts/FuturaPT-Demi.woff) format('woff');
            font-weight: 450;
            font-style: normal;

        }

        .top .top-left .design-menu {
            font-family: "FuturaPT";
        }
        .top .top-left .action-choose-template {
            font-family: "FuturaPT";
        }
        body {
            font-family: "FuturaPT";
            background-color: var(--color-surface);
        }
        .top .top-left .action {
            font-family: "FuturaPT";
         }
        .top .top-right ul.icons li span.help {

            font-family: "FuturaPT";
        }
        .top .top-left .view-mode {

            font-family: "FuturaPT";
        }
        .top .top-left .action ul li a {

            font-family: "FuturaPT";
        }
        .top .top-left .design-menu ul li a {

            font-family: "FuturaPT";
        }
        .top .top-left .action-choose-template ul li a {

            font-family: "FuturaPT";
        }
        .top .top-left .view-mode ul li a {

            font-family: "FuturaPT";
        }
        /*.wrapper .top {*/

        /*  background: #fff;*/

        /*}*/

        /*.top .top-left .design-menu span.action-design {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .design-menu span.action-design:hover {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .design-menu:hover span.action-design {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .design-menu.add-background-design .span.action-design {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .design-menu.add-background-design .span.action-design:hover  {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .design-menu:hover span.action-design {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .design-menu span.action-design {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .design-menu span.action-design:hover {*/
        /*  color: #000;*/
        /*}*/

        /*.top .top-left .action-choose-template span.choose {*/
        /*  color: #000;*/
        /*}*/

        /*.top .top-left .action-choose-template span.choose:hover {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .action-choose-template span.choose {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .action-choose-template span.choose:hover {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .action span.ac {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .action span.ac:hover {*/
        /*  color: #000;*/
        /*}*/

        /*.top .top-left .view-mode span.ac {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .view-mode span.ac:hover {*/
        /*  color: #000;*/
        /*}*/
        /*.top .top-left .design-menu ul li:hover {*/
        /*  background: #e0e0e0;*/
        /*}*/
        /*.top .top-left .add-background-choose span.choose {*/
        /*  color: #fff;*/
        /*}*/
        /*.top .top-left .add-background-choose span.choose:hover {*/
        /*  color: #fff;*/
        /*}*/
        /*.top .top-left .add-background-design span.action-design  {*/
        /*  color: #fff;*/
        /*}*/
        /*.top .top-left .add-background-design span.action-design:hover {*/
        /*  color: #fff;*/
        /*}*/
        /*.top .top-right .menu-bar-action.btn-save {*/
        /*  background: #000;*/
        /*}*/
        /*.top .top-right .mode-device .icon-mode {*/
        /*  color: #000;*/
        /*}*/
    </style>
    <script>
        var CSRF_TOKEN = "{{ csrf_token() }}";
        var editor;

        var templates = {!! json_encode($templates) !!};

        $(document).ready(function () {
            editor = new Editor({
                buildMode: false,
                legacyMode: true,
                url: '{{ action('TemplateController@builderEditContent', $template->uid) }}',
                backCallback: function () {
                    parent.$('.full-iframe-popup').fadeOut();
                    parent.$('body').removeClass('overflow-hidden');
                },
                uploadAssetUrl: '{{ action('TemplateController@builderAsset', $template->uid) }}',
                uploadAssetMethod: 'POST',
                saveUrl: '{{ action('TemplateController@builderEdit', $template->uid) }}',
                saveMethod: 'POST',
                tags: {!! json_encode(Acelle\Model\Template::builderTags((isset($list) ? $list : null))) !!},
                root: '{{ URL::asset('builder') }}/',
                templates: templates,
                logo: '{{ \Acelle\Model\Setting::get('site_logo_small') ? action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) : URL::asset('images/logo_emailwish.webp') }}',
                backgrounds: [
                    '{{ url('/images/backgrounds/images1.jpg') }}',
                    '{{ url('/images/backgrounds/images2.jpg') }}',
                    '{{ url('/images/backgrounds/images3.jpg') }}',
                    '{{ url('/images/backgrounds/images4.png') }}',
                    '{{ url('/images/backgrounds/images5.jpg') }}',
                    '{{ url('/images/backgrounds/images6.jpg') }}',
                    '{{ url('/images/backgrounds/images9.jpg') }}',
                    '{{ url('/images/backgrounds/images11.jpg') }}',
                    '{{ url('/images/backgrounds/images12.jpg') }}',
                    '{{ url('/images/backgrounds/images13.jpg') }}',
                    '{{ url('/images/backgrounds/images14.jpg') }}',
                    '{{ url('/images/backgrounds/images15.jpg') }}',
                    '{{ url('/images/backgrounds/images16.jpg') }}',
                    '{{ url('/images/backgrounds/images17.png') }}'
                ],
                customInlineEdit: function (container) {
                    var tinyconfig = {
                        skin: 'oxide-dark',
                        inline: true,
                        menubar: false,
                        force_br_newlines: false,
                        force_p_newlines: false,
                        forced_root_block: '',
                        inline_boundaries: false,
                        relative_urls: false,
                        convert_urls: false,
                        remove_script_host: false,
                        valid_elements: '*[*],meta[*]',
                        valid_children: '+h1[div],+h2[div],+h3[div],+h4[div],+h5[div],+h6[div],+a[div]',
                        plugins: 'image link textcolor lists autolink',
                        //toolbar: 'undo redo | bold italic underline | fontselect fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignfull | numlist bullist outdent indent',
                        toolbar: [
                            'undo redo | bold italic underline | fontselect fontsizeselect | link | menuDateButton',
                            // 'forecolor backcolor | alignleft aligncenter alignright alignfull | numlist bullist outdent indent'
                        ],
                        external_filemanager_path: '{{ url('/') }}'.replace('/index.php', '') + "/filemanager2/",
                        filemanager_title: "Responsive Filemanager",
                        external_plugins: {"filemanager": '{{ url('/') }}'.replace('/index.php', '') + "/filemanager2/plugin.min.js"},
                        setup: function (editor) {

                            /* Menu button that has a simple "insert date" menu item, and a submenu containing other formats. */
                            /* Clicking the first menu item or one of the submenu items inserts the date in the selected format. */
                            editor.ui.registry.addMenuButton('menuDateButton', {
                                text: getI18n('editor.insert_tag'),
                                fetch: function (callback) {
                                    var items = [];

                                    thisEditor.tags.forEach(function (tag) {
                                        if (tag.type == 'label') {
                                            items.push({
                                                type: 'menuitem',
                                                text: tag.tag.replace("{", "").replace("}", ""),
                                                onAction: function (_) {
                                                    if (tag.text) {
                                                        editor.insertContent(tag.text);
                                                    } else {
                                                        editor.insertContent(tag.tag);
                                                    }
                                                }
                                            });
                                        }
                                    });

                                    callback(items);
                                }
                            });
                        }
                    };

                    container.addClass('builder-class-tinymce');
                    tinyconfig.selector = '.builder-class-tinymce';
                    $("#builder_iframe")[0].contentWindow.tinymce.init(tinyconfig);

                    container.removeClass('builder-class-tinymce');
                }
            });

            editor.init();
        });
    </script>
</head>
<body>
</body>
</html>
