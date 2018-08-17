<?php

namespace PiggyShopUI;

use jojoe77777\FormAPI\FormAPI;
use onebone\economyapi\EconomyAPI;
use PiggyShopUI\Commands\BuyCommand;
use PiggyShopUI\Commands\SellCommand;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

/**
 * Class Main
 * @package PiggyShopUI
 */
class Main extends PluginBase
{
    /** @var array */
    public $buyCategories = [];
    /** @var array */
    public $sellCategories = [];

    public function onEnable()
    {
        if (is_null($this->getServer()->getPluginManager()->getPlugin("FormAPI"))) {
            $this->getLogger()->error("FormAPI is required, but not found.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        if (is_null($this->getServer()->getPluginManager()->getPlugin("EconomyAPI"))) {
            $this->getLogger()->error("EconomyAPI is required, but not found.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $this->saveDefaultConfig();
        $this->loadCategories();

        $this->getServer()->getCommandMap()->register("buy", new BuyCommand("buy", $this));
        $this->getServer()->getCommandMap()->register("sell", new SellCommand("sell", $this));

        $this->getLogger()->info(TextFormat::GREEN . "Enabled.");
    }

    public function loadCategories()
    {
        foreach ($this->getConfig()->getNested("buy-categories") as $category) {
            if (!isset($category["name"])) continue;
            if (!isset($category["items"])) continue;
            $parsedItemData = [];
            foreach ($category["items"] as $itemData) {
                if (!isset($itemData["menu-name"])) continue;
                if (!isset($itemData["id"])) continue;
                if (!isset($itemData["price"])) continue;
                $parsedItemData[$itemData["menu-name"]] = $itemData;
            }
            $category["items"] = $parsedItemData;
            $this->buyCategories[$category["name"]] = $category;
        }
        foreach ($this->getConfig()->getNested("sell-categories") as $category) {
            if (!isset($category["name"])) continue;
            if (!isset($category["items"])) continue;
            $parsedItemData = [];
            foreach ($category["items"] as $itemData) {
                if (!isset($itemData["menu-name"])) continue;
                if (!isset($itemData["id"])) continue;
                if (!isset($itemData["price"])) continue;
                $parsedItemData[$itemData["menu-name"]] = $itemData;
            }
            $category["items"] = $parsedItemData;
            $this->sellCategories[$category["name"]] = $category;
        }
    }

    /**
     * @param Player $player
     */
    public function openBuyMainMenu(Player $player)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($formapi instanceof FormAPI && $formapi->isEnabled()) {
            $form = $formapi->createSimpleForm(function (Player $player, ?int $data) {
                if (!is_null($data)) {
                    $this->openBuyCategoryMenu($player, array_keys($this->buyCategories)[$data]);
                }
            });
            $form->setTitle($this->getConfig()->getNested("buy-main-menu-title"));
            foreach ($this->buyCategories as $name => $category) {
                $form->addButton($name, (isset($category["image-type"]) ? (int)$category["image-type"] : -1), (isset($category["image"]) ? $category["image"] : ""));
            }
            $form->sendToPlayer($player);
        }
    }

    /**
     * @param Player $player
     * @param string $categoryName
     */
    public function openBuyCategoryMenu(Player $player, string $categoryName)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($formapi instanceof FormAPI && $formapi->isEnabled()) {
            $form = $formapi->createSimpleForm(function (Player $player, ?int $data) use ($categoryName) {
                if (!is_null($data)) {
                    $this->openBuyItemMenu($player, $this->buyCategories[$categoryName]["items"][array_keys($this->buyCategories[$categoryName]["items"])[$data]]);
                }
            });
            $form->setTitle(str_replace("{CATEGORY}", $categoryName, $this->getConfig()->getNested("buy-category-menu-title")));
            foreach ($this->buyCategories[$categoryName]["items"] as $menuName => $itemData) {
                $form->addButton($menuName, (isset($itemData["image-type"]) ? (int)$itemData["image-type"] : -1), (isset($itemData["image"]) ? $itemData["image"] : ""));
            }
            $form->sendToPlayer($player);
        }
    }

    /**
     * @param Player $player
     * @param array $itemData
     */
    public function openBuyItemMenu(Player $player, array $itemData)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($formapi instanceof FormAPI && $formapi->isEnabled()) {
            $form = $formapi->createCustomForm(function (Player $player, ?array $data) use ($itemData) {
                if (!is_null($data) && isset($data[1])) {
                    $this->openBuyItemConfirmationMenu($player, $itemData, (int)$data[1]);
                }
            });
            $form->setTitle(str_replace("{ITEM}", $itemData["menu-name"], $this->getConfig()->getNested("buy-item-menu-title")));
            $form->addLabel(str_replace(["{ITEM}", "{PRICE}"], [$itemData["menu-name"], $itemData["price"]], $this->getConfig()->getNested("messages.buy-item-information")));
            $form->addInput("Count", 1, 1);
            $form->sendToPlayer($player);
        }
    }

    /**
     * @param Player $player
     * @param array $itemData
     * @param int $count
     */
    public function openBuyItemConfirmationMenu(Player $player, array $itemData, int $count)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($formapi instanceof FormAPI && $formapi->isEnabled()) {
            $form = $formapi->createModalForm(function (Player $player, ?bool $data) use ($itemData, $count) {
                if (!is_null($data)) {
                    if ($data) {
                        $cost = $itemData["price"] * $count;
                        $economyapi = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
                        if ($economyapi instanceof EconomyAPI && $economyapi->isEnabled()) {
                            $money = $economyapi->myMoney($player);
                            if ($money < $cost) {
                                $player->sendMessage(str_replace(["{COUNT}", "{ITEM}", "{MISSING}"], [$count, $itemData["menu-name"], ($cost - $money)], $this->getConfig()->getNested("messages.not-enough-money")));
                                return;
                            }
                            $economyapi->reduceMoney($player, $cost);
                            $player->sendMessage(str_replace(["{COUNT}", "{ITEM}", "{PRICE}"], [$count, $itemData["menu-name"], $cost], $this->getConfig()->getNested("messages.successfully-bought-item")));
                            $player->getInventory()->addItem(Item::get((int)$itemData["id"], isset($itemData["damage"]) ? (int)$itemData["damage"] : 0, $count));
                        }
                    }
                }
            });
            $form->setTitle($this->getConfig()->getNested("buy-item-confirm-menu-title"));
            $form->setContent(str_replace(["{COUNT}", "{ITEM}", "{PRICE}"], [$count, $itemData["menu-name"], $itemData["price"] * $count], $this->getConfig()->getNested("messages.buy-item-confirmation")));
            $form->setButton1("Confirm");
            $form->setButton2("Cancel");
            $form->sendToPlayer($player);
        }
    }

    /**
     * @param Player $player
     */
    public function openSellMainMenu(Player $player)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($formapi instanceof FormAPI && $formapi->isEnabled()) {
            $form = $formapi->createSimpleForm(function (Player $player, ?int $data) {
                if (!is_null($data)) {
                    $this->openSellCategoryMenu($player, array_keys($this->sellCategories)[$data]);
                }
            });
            $form->setTitle($this->getConfig()->getNested("sell-main-menu-title"));
            foreach ($this->sellCategories as $name => $category) {
                $form->addButton($name, (isset($category["image-type"]) ? (int)$category["image-type"] : -1), (isset($category["image"]) ? $category["image"] : ""));
            }
            $form->sendToPlayer($player);
        }
    }

    /**
     * @param Player $player
     * @param string $categoryName
     */
    public function openSellCategoryMenu(Player $player, string $categoryName)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($formapi instanceof FormAPI && $formapi->isEnabled()) {
            $form = $formapi->createSimpleForm(function (Player $player, ?int $data) use ($categoryName) {
                if (!is_null($data)) {
                    $this->openSellItemMenu($player, $this->sellCategories[$categoryName]["items"][array_keys($this->sellCategories[$categoryName]["items"])[$data]]);
                }
            });
            $form->setTitle(str_replace("{CATEGORY}", $categoryName, $this->getConfig()->getNested("sell-category-menu-title")));
            foreach ($this->sellCategories[$categoryName]["items"] as $menuName => $itemData) {
                $form->addButton($menuName, (isset($itemData["image-type"]) ? (int)$itemData["image-type"] : -1), (isset($itemData["image"]) ? $itemData["image"] : ""));
            }
            $form->sendToPlayer($player);
        }
    }

    /**
     * @param Player $player
     * @param array $itemData
     */
    public function openSellItemMenu(Player $player, array $itemData)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($formapi instanceof FormAPI && $formapi->isEnabled()) {
            $form = $formapi->createCustomForm(function (Player $player, ?array $data) use ($itemData) {
                if (!is_null($data) && isset($data[1])) {
                    $this->openSellItemConfirmationMenu($player, $itemData, (int)$data[1]);
                }
            });
            $form->setTitle(str_replace("{ITEM}", $itemData["menu-name"], $this->getConfig()->getNested("sell-item-menu-title")));
            $form->addLabel(str_replace(["{ITEM}", "{PRICE}"], [$itemData["menu-name"], $itemData["price"]], $this->getConfig()->getNested("messages.sell-item-information")));
            $form->addInput("Count", 1, 1);
            $form->sendToPlayer($player);
        }
    }

    /**
     * @param Player $player
     * @param array $itemData
     * @param int $count
     */
    public function openSellItemConfirmationMenu(Player $player, array $itemData, int $count)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($formapi instanceof FormAPI && $formapi->isEnabled()) {
            $form = $formapi->createModalForm(function (Player $player, ?bool $data) use ($itemData, $count) {
                if (!is_null($data)) {
                    if ($data) {
                        $economyapi = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
                        if ($economyapi instanceof EconomyAPI && $economyapi->isEnabled()) {
                            $paid = $itemData["price"] * $count;
                            if (!$player->getInventory()->contains(Item::get((int)$itemData["id"], isset($itemData["damage"]) ? (int)$itemData["damage"] : 0, $count))) {
                                $player->sendMessage(str_replace(["{COUNT}", "{ITEM}"], [$count, $itemData["menu-name"]], $this->getConfig()->getNested("messages.missing-items")));
                                return;
                            }
                            $economyapi->addMoney($player, $paid);
                            $player->sendMessage(str_replace(["{COUNT}", "{ITEM}", "{PRICE}"], [$count, $itemData["menu-name"], $paid], $this->getConfig()->getNested("messages.successfully-sold-item")));
                            $player->getInventory()->removeItem(Item::get((int)$itemData["id"], isset($itemData["damage"]) ? (int)$itemData["damage"] : 0, $count));
                        }
                    }
                }
            });
            $form->setTitle($this->getConfig()->getNested("sell-item-confirm-menu-title"));
            $form->setContent(str_replace(["{COUNT}", "{ITEM}", "{PRICE}"], [$count, $itemData["menu-name"], $itemData["price"] * $count], $this->getConfig()->getNested("messages.sell-item-confirmation")));
            $form->setButton1("Confirm");
            $form->setButton2("Cancel");
            $form->sendToPlayer($player);
        }
    }
}