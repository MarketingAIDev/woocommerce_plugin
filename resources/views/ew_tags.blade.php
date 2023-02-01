<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <style>
        body {margin:0;overflow-x:hidden;overflow-y:auto;}
        .container {}
        .container > div {
            text-align:center;
            font-size:16px;
            cursor:pointer;
            margin: 0;
            display:inline-block;
            float:left;
            width:100%;
            height:50px;
            line-height:50px;
            border:#eee 1px solid;
            box-sizing:border-box;
        }
        .clearfix:before, .clearfix:after {content: " ";display: table;}
        .clearfix:after {clear: both;}
        .clearfix {zoom: 1;}

        .tab-content {
            position:fixed;width:100%;height:calc((100% - 40px));top:40px;left:0;overflow-x:hidden;overflow-y:auto;
            display: none;
            flex-flow: wrap;
            align-content: start;
        }
        .tab-content > div {min-width:65px}
        *::-webkit-scrollbar {
        width: 8px;
        height: 8px;
        }

        *::-webkit-scrollbar-track {
        background: #eee;
        /* border-radius: 20px; */
        }

        *::-webkit-scrollbar-thumb {
        background-color: rgb(162, 162, 162);
        /* border-radius: 20px; */
        border: 3px solid #eee;
        }
    </style>
</head>
<body>

<div class="tab-content container clearfix" style="display:flex">
@foreach($tags as $tag)
@if($tag['name'] != 'UNSUBSCRIBE_URL' && $tag['name'] != 'WEB_VIEW_URL' && $tag['name'] != 'UNSUBSCRIBE_LINK' && $tag['name'] != 'WEB_VIEW_LINK')
  <div title="{<?= $tag['name'] ?>}"><?= $tag['name'] ?></div>  
@endif

@endforeach
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    function clearSelection() {
        if (window.getSelection) {
            if (window.getSelection().empty) {  // Chrome
                window.getSelection().empty();
            } else if (window.getSelection().removeAllRanges) {  // Firefox
                window.getSelection().removeAllRanges();
            }
        } else if (document.selection) {  // IE?
            if (document.selection.empty)
                document.selection.empty();
        }
    }

    jQuery(document).ready(function () {
        jQuery('.container > div').click(function () {
            const s = jQuery(this).attr('title');
            parent._cb.saveForUndo(true);
            parent.pasteHtmlAtCaretNew(s, false);
            clearSelection();
        });
    });

</script>
</body>
</html>
