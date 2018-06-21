<?php

include 'phpqrcode/qrlib.php';

class AjaxHandler
{
    private $aHandlers = [];
    private $sKeyWordName = 'operation';

    public function RegisterHandler($sKeyWord, Closure $fnHandler)
    {
        $this->aHandlers[$sKeyWord] = $fnHandler;
    }

    public function Execute()
    {
        try {
            require_once('../../approot.inc.php');
            require_once(APPROOT . '/application/application.inc.php');
            require_once(APPROOT . '/application/webpage.class.inc.php');
            require_once(APPROOT . '/application/ajaxwebpage.class.inc.php');
            require_once(APPROOT . '/application/startup.inc.php');
            require_once(APPROOT . '/application/loginwebpage.class.inc.php');
            LoginWebPage::DoLoginEx(null /* any portal */, false);
            $oPage = new ajax_page("");
            $oPage->no_cache();
            $sKeyWord = utils::ReadParam($this->sKeyWordName, '');
            if (isset($this->aHandlers[$sKeyWord]) && $this->aHandlers[$sKeyWord] instanceof Closure) {
                $fnHandler = $this->aHandlers[$sKeyWord];
                $fnHandler($oPage);
            } else {
                $oPage->p("Missing argument 'operation' or route not found");
            }
            $oPage->output();
        } catch (Exception $e) {
            // note: transform to cope with XSS attacks
            echo htmlentities($e->GetMessage(), ENT_QUOTES, 'utf-8');
            IssueLog::Error($e->getMessage());
        }
    }
}

$oHandler = new AjaxHandler();
$oHandler->RegisterHandler('generate_qr', function (ajax_page $oP) {
    $sObjClass = stripslashes(utils::ReadParam('obj_class', '', false, 'class'));
    $iObjKey = (int)utils::ReadParam('obj_key', 0);
    if (empty($sObjClass) || $iObjKey <= 0) {
        throw new ApplicationException(Dict::Format('UI:Error:2ParametersMissing', 'obj_class', 'obj_key'));
    }
    $oObj = MetaModel::GetObject($sObjClass, $iObjKey);
    $oP->SetContentType('image/png');
    $oP->SetContentDisposition('attachment', "QR_{$sObjClass}_{$iObjKey}.png");
    $sData = '';
    if (!MetaModel::GetModuleSetting('knowitop-qr-code', 'url_only', true)) {
        $sData .= 'name: ' . $oObj->GetName() . "\n";
        $sData .= 's/n: ' . $oObj->Get('serialnumber') . "\n";
        $sData .= 'asset: ' . $oObj->Get('asset_number') . "\n";
    }
    $sData .= ApplicationContext::MakeObjectUrl($sObjClass, $iObjKey, null, false);
    QRcode::png($sData);
});
$oHandler->Execute();
