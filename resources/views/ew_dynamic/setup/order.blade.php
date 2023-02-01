@extends('ew_dynamic.setup.base')

@section('content')
    <svg width="0" height="0" style="position:absolute;display:none;">
        <defs>
            <symbol viewBox="0 0 512 512" id="ion-ios-close-empty">
                <path d="M340.2 160l-84.4 84.3-84-83.9-11.8 11.8 84 83.8-84 83.9 11.8 11.7 84-83.8 84.4 84.2 11.8-11.7-84.4-84.3 84.4-84.2z"></path>
            </symbol>
            <symbol viewBox="0 0 512 512" id="ion-contrast">
                <path d="M256 32C132.3 32 32 132.3 32 256s100.3 224 224 224 224-100.3 224-224S379.7 32 256 32zm135.8 359.8C355.5 428 307 448 256 448V64c51 0 99.5 20 135.8 56.2C428 156.5 448 204.7 448 256c0 51.3-20 99.5-56.2 135.8z"></path>
            </symbol>
        </defs>
    </svg>

    <div id="dynamicOrder" class="is-tab-content" data-group="button" style="display:block">
        <div style="width:100%;">
            <div style="width:24%;display:inline-block;">
                <span>Order Source:</span>
            </div>
            <div style="width:70%;display:inline-block;">
                <select style="margin-top:0" id="input_source" oninput="updateIframeUrl()">
                    <option value="automation">From Automation Trigger</option>
                </select>
            </div>
        </div>

        <div style="width:100%;">
            <div style="width:24%;display:inline-block;">
                <span>Include Images:</span>
            </div>
            <div style="width:70%;display:inline-block;margin:5px 0">
                <input id="input_images" type="checkbox" oninput="updateIframeUrl()"/>
            </div>
        </div>

        <div style="width:100%;">
            <div style="width:24%;display:inline-block;margin:10px 0 0 0">
                <span>General<br />Colors:</span>
            </div>
            <div style="width:24%;display:inline-block">
                <label for="input_text_background_color" style="margin:8px 0 0 0">Background</label><br/>
                <input id="input_text_background_color" type="color" oninput="updateIframeUrl()"/>
            </div>
            <div style="width:24%;display:inline-block">
                <label for="input_text_color" style="margin:8px 0 0 0">Text</label><br/>
                <input id="input_text_color" type="color" oninput="updateIframeUrl()"/>
            </div>
        </div>

        <div style="width:100%;">
            <div style="width:24%;display:inline-block;margin:10px 0 0 0">
                <span>Button<br />Colors:</span>
            </div>
            <div style="width:24%;display:inline-block">
                <label for="input_button_color" style="margin:8px 0 0 0">Button</label><br/>
                <input id="input_button_color" type="color" oninput="updateIframeUrl()"/>
            </div>
            <div style="width:24%;display:inline-block">
                <label for="input_button_text_color" style="margin:8px 0 0 0">Text</label><br/>
                <input id="input_button_text_color" type="color" oninput="updateIframeUrl()"/>
            </div>
            <div style="width:24%;display:inline-block">
                <label for="input_button_border_color" style="margin:8px 0 0 0">Border</label><br/>
                <input id="input_button_border_color" type="color" oninput="updateIframeUrl()"/>
            </div>
        </div>

        <div style="width:100%;">
            <div style="width:24%;display:inline-block;">
                <span>Button Text:</span>
            </div>
            <div style="width:70%;display:inline-block;margin:5px 0">
                <input id="input_button_text" type="text" oninput="updateIframeUrl()"/>
            </div>
        </div>
    </div>

    <script>
        const activeIframe = parent._cb.activeElement.querySelector('iframe');

        const input_source = document.getElementById("input_source")
        const input_order_id = document.getElementById("input_order_id")

        const input_images = document.getElementById("input_images")
        const input_text_background_color = document.getElementById("input_text_background_color")
        const input_text_color = document.getElementById("input_text_color")
        const input_button_text = document.getElementById("input_button_text")
        const input_button_color = document.getElementById("input_button_color")
        const input_button_border_color = document.getElementById("input_button_border_color")
        const input_button_text_color = document.getElementById("input_button_text_color")

        function updateIframeUrl() {
            let activeIframeUrl = new URL(activeIframe.src);
            activeIframeUrl.searchParams.set('source', input_source.value);
            activeIframeUrl.searchParams.set('images', input_images.checked ? "Y" : "N");
            activeIframeUrl.searchParams.set('text_background_color', input_text_background_color.value);
            activeIframeUrl.searchParams.set('text_color', input_text_color.value);
            activeIframeUrl.searchParams.set('button_text', input_button_text.value);
            activeIframeUrl.searchParams.set('button_color', input_button_color.value);
            activeIframeUrl.searchParams.set('button_border_color', input_button_border_color.value);
            activeIframeUrl.searchParams.set('button_text_color', input_button_text_color.value);

            activeIframe.contentWindow.location.replace(activeIframeUrl.toString())
        }

        function updateElements() {
            let activeIframeUrl = new URL(activeIframe.src);

            let input_source_options_length = input_source.options.length;
            for (let i = 0; i < input_source_options_length; i++) {
                let option = input_source.options[i];
                option.selected = option.value === activeIframeUrl.searchParams.get('source')
            }
            input_images.checked = activeIframeUrl.searchParams.get('images') === "Y";
            input_text_background_color.value = activeIframeUrl.searchParams.get('text_background_color') || "#ffffff";
            input_text_color.value = activeIframeUrl.searchParams.get('text_color') || "#000000";
            input_button_text.value = activeIframeUrl.searchParams.get('button_text') || "View";
            input_button_color.value = activeIframeUrl.searchParams.get('button_color') || "#ffffff";
            input_button_border_color.value = activeIframeUrl.searchParams.get('button_border_color') || "#000000";
            input_button_text_color.value = activeIframeUrl.searchParams.get('button_text_color') || "#000000";
        }

        updateElements();
    </script>
@endsection