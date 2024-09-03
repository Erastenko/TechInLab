<?php

use \Bitrix\Main\Loader,
\Bitrix\Main\Localization\Loc;

Loader::includeModule('catalog');
Loader::includeModule('sale');

\Bitrix\Main\Loader::includeModule('highloadblock');
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;


AddEventHandler('sale', 'OnSaleBasketItemRefreshData', ['\BasketEventCustomRefresh', 'BeforeBasketAddHandler']);

class CCatalogProductCustomPrice extends CCatalogProductProvider
{
    public static function GetProductData($arParams)
    {

        $arResult = parent::GetProductData($arParams);
        $salePrice = GetSalePriceHL($arParams["PRODUCT_ID"]);
        if (!empty($arItemPrice)) {
            $arResult = [
                'BASE_PRICE' => $salePrice,
            ] + $arResult;
        }

        return $arResult;
    }
}

class BasketEventCustomRefresh 
{
    public static function BeforeBasketAddHandler($BasketItem)
    {
    
        $BasketItem->setField("PRODUCT_PROVIDER_CLASS", "CCatalogProductCustomPrice");
    }
}

function GetSalePriceHL($productID)
{
    $entity_data_class = HL\HighloadBlockTable::compileEntity('PriceBasket')->getDataClass();
   
    $arItemPrice = $entity_data_class::getList([ 
        "select" => ["UF_PRICE_PRODUCT"],
        "order" => ["ID" => "DESC"],
        'limit' => '1', 
        "filter" => [
            "UF_ID_PRODUCT" => $productID,
            "!=UF_PRICE_PRODUCT" =>0,
        ],
    ])->Fetch();

return $arItemPrice;
}