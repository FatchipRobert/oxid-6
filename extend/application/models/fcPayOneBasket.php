<?php

/** 
 * PAYONE OXID Connector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PAYONE OXID Connector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with PAYONE OXID Connector.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.payone.de
 * @copyright (C) Payone GmbH
 * @version   OXID eShop CE
 */
 
class fcPayOneBasket extends fcPayOneBasket_parent
{
    /**
     * Helper object for dealing with different shop versions
     *
     * @var object
     */
    protected $_oFcpoHelper = null;

    /**
     * init object construction
     * 
     * @return null
     */
    public function __construct() 
    {
        parent::__construct();
        $this->_oFcpoHelper = oxNew('fcpohelper');
    }

    /**
     * Iterates through basket items and calculates its delivery costs
     *
     * @return oxPrice
     */
    public function fcpoCalcDeliveryCost()
    {
        $myConfig = $this->getConfig();
        $oDeliveryPrice = oxNew('oxprice');
        if ($this->getConfig()->getConfigParam('blDeliveryVatOnTop')) {
            $oDeliveryPrice->setNettoPriceMode();
        } else {
            $oDeliveryPrice->setBruttoPriceMode();
        }
        $oUser = oxNew('oxUser');
        $oUser->oxuser__oxcountryid = new oxField('a7c40f631fc920687.20179984');
        $sDelCountry = $this->_findDelivCountry();
        if (!$sDelCountry) {
            $sDelCountry = $oUser->oxuser__oxcountryid->value;
        }
        $fDelVATPercent = $this->getAdditionalServicesVatPercent();
        $oDeliveryPrice->setVat($fDelVATPercent);
        $aDeliveryList = oxRegistry::get("oxDeliveryList")->getDeliveryList(
            $this,
            $oUser,
            $sDelCountry,
            $this->getShippingId()
        );
        if (count($aDeliveryList) > 0) {
            foreach ($aDeliveryList as $oDelivery) {
                //debug trace
                if ($myConfig->getConfigParam('iDebug') == 5) {
                    echo("DelCost : " . $oDelivery->oxdelivery__oxtitle->value . "<br>");
                }
                $oDeliveryPrice->addPrice($oDelivery->getDeliveryPrice($fDelVATPercent));
            }
        }

        return $oDeliveryPrice;
    }
}
