<div @if(!empty($theme['theme_font_family']))style="font-family: {{$theme['theme_font_family']}}" @endif>
    <div>
        <div id="ew_review_result" style="display:none;"></div>
        <form id="ew_review_form" action="{{action('ShopifyReviewController@storeFromReviewer')}}" method="post">
            {{csrf_field()}}
            <input type="hidden" name="shop_name" value="{{$shop_name}}"/>
            <input type="hidden" name="product_id" value="{{$product_id}}"/>
            <table>
                <tr>
                    <td style="border:none;">
                        <input required=true" name="title" placeholder="Review title"/>
                    </td>
                    <td style="border:none;">
                        <select required=true" name="stars">
                            <option disabled>Rating</option>
                            <option value="5" selected>⭐⭐⭐⭐⭐</option>
                            <option value="4">⭐⭐⭐⭐</option>
                            <option value="3">⭐⭐⭐</option>
                            <option value="2">⭐⭐</option>
                            <option value="1">⭐</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="border:none;">
                        <input required=true" name="reviewer_name" placeholder="Your name"/>
                    </td>
                    <td style="border:none;">
                        <input required=true" type="email" name="reviewer_email" placeholder="Your email"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="border:none;">
                        <textarea required=true" name="message" placeholder="Your review"></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="border:none;">
                        <button type="submit"
                                @if(!empty($theme['buy_now_btn_color'] && !empty($theme['buy_now_btn_text_color'])))
                                style="background-color:{{$theme['buy_now_btn_color']}};color:{{$theme['buy_now_btn_text_color']}}"
                                @endif>Submit review
                        </button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    @foreach($items as $item)
        <div style="display:inline-block;margin:5px;padding:10px;border:1px solid black;max-width:300px;max-height:200px;overflow:auto">
            <h4 style="margin:0; padding:0"><strong>{{$item->title}}</strong></h4>
            <h4 style="margin:0; padding:0">
                @for ($i = 0; $i < $item->stars; $i++)⭐@endfor
            </h4>
            <h5 style="margin:0; padding:0"><strong>{{$item->reviewer_name}}</strong></h5>
            <p style="margin:0; padding:0">{{$item->message}}</p>
            <span><i>{{Acelle\Library\Tool::formatDate(new Carbon\Carbon($item->created_at))}}</i></span>
        </div>
    @endforeach
</div>