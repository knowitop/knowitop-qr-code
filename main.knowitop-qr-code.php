<?php

class QRCodeMenuExtension implements iPopupMenuExtension {

    public static function EnumItems($iMenuId, $oObject)
    {
        $aMenuItems = [];
        if ($iMenuId === self::MENU_OBJDETAILS_ACTIONS && self::IsTarget($oObject)) {
            $sClass = get_class($oObject);
            $iKey = $oObject->GetKey();
            $sURL = utils::GetAbsoluteUrlModulesRoot() . "knowitop-qr-code/ajax.php?operation=generate_qr&obj_key=$iKey&obj_class=$sClass";
            $aMenuItems[] = new SeparatorPopupMenuItem();
            $aMenuItems[] = new URLPopupMenuItem('QRCodeExtension-generate', Dict::S('QRCodeExtension:Generate'), $sURL);
        }
        return $aMenuItems;
    }

    private static function IsTarget($oObj) {
        return $oObj instanceof PhysicalDevice;
    }

}