<p><?=GetMessage("WIZ_STEP_DEMO_FORM_DESC")?></p>
<div class="wrp-demo">
    <div class="left-demo">
        <script id="bx24_form_inline" data-skip-moving="true">
            (function(w,d,u,b){w['Bitrix24FormObject']=b;w[b] = w[b] || function(){arguments[0].ref=u;
                    (w[b].forms=w[b].forms||[]).push(arguments[0])};
                if(w[b]['forms']) return;
                var s=d.createElement('script');s.async=1;s.src=u+'?'+(1*new Date());
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
            })(window,document,'https://krayt.bitrix24.ru/bitrix/js/crm/form_loader.js','b24form');

            b24form({"id":"68","lang":"ru","sec":"l5pub2","type":"inline"});
        </script>
    </div>
    <div class="rigth-demo">
        <div>
            <div class="demo-manager-photo"></div>
        </div>
        <div class="demo-manager-prof"><?=GetMessage("WIZ_STEP_DEMO_FORM_MANAGER")?></div>
        <div class="demo-manager-name"><?=GetMessage("WIZ_STEP_DEMO_FORM_MANAGER_NAME")?></div>
        <div class="demo-manager-desc">
            <?=GetMessage("WIZ_STEP_DEMO_FORM_MANAGER_DESC")?>
        </div>
    </div>
    <div class="clear-demo"></div>
</div>
<div style="clear: both"></div>
<style>
    .wrp-demo{
        height: 265px;
    }
    .left-demo{
        width: 60%;
        float: left;
    }
    .rigth-demo{
        float: right;
        width: 40%;
    }
    .clear-demo{
        clear: both;
    }
    .wizard-field-demo{
        width: 100%;
        box-sizing: border-box;
    }
    .left-demo-item{
        margin-bottom: 30px;
    }
    .left-demo-item label{
        font-weight: 600;
        margin-bottom: 5px;
        display: block;
    }
    .left-demo-item .error{
        display: block;
        color: red;
        font-size: 12px;
        margin-top: 5px;
    }
    .demo-manager-photo{
        width: 120px;
        height: 120px;
        background-color: #eee;
        border-radius: 50%;
        margin: 0 auto;
        margin-top: 12px;
        background-image: url('https://krayt.ru/upload/manager.png');
        background-position: center;
        background-size: contain;
    }
    .demo-manager-prof{
        font-size: 12px;
        text-align: center;
        margin-top: 10px;
    }
    .demo-manager-name{
        text-align: center;
        font-weight: bold;
        margin-top: 10px;
    }
    .demo-manager-desc{
        text-align: center;
        font-size: 12px;
        margin-top: 10px;
    }
    .left-demo-agree{
        font-size: 12px;
    }
</style>

