<?php

namespace DaPigGuy\PiggyShopUI;

use DaPigGuy\PiggyShopUI\commands\ShopCommand;
use DaPigGuy\PiggyShopUI\economy\EconomyProvider;
use DaPigGuy\PiggyShopUI\economy\EconomySProvider;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use DaPigGuy\PiggyShopUI\shops\ShopItem;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

/**
 * Class PiggyShopUI
 * @package DaPigGuy\PiggyShopUI
 */
class PiggyShopUI extends PluginBase
{
    /** @var PiggyShopUI */
    public static $instance;

    /** @var ShopCategory[] */
    public $shopCategories = [];
    /** @var Config */
    public $shopConfig;

    /** @var EconomyProvider */
    public $economyProvider;

    public function onEnable(): void
    {
        self::$instance = $this;

        $this->saveDefaultConfig();

        switch (strtolower($this->getConfig()->getNested("economy.provider"))) {
            case "economys":
            default:
                if ($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") === null) {
                    $this->getLogger()->error("EconomyAPI is required for your selected economy provider.");
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                    return;
                }
                $this->economyProvider = new EconomySProvider();
                break;
        }

        $this->shopConfig = new Config($this->getDataFolder() . "shops.yml");
        foreach ($this->shopConfig->getAll() as $category) {
            $this->shopCategories[$category["name"]] = new ShopCategory($category["name"], array_map(function (array $item): ShopItem {
                return new ShopItem(Item::jsonDeserialize($item["item"]), $item["description"], $item["buyPrice"], $item["canSell"], $item["sellPrice"]);
            }, $category["items"]), $category["private"]);
        }

        $this->getServer()->getCommandMap()->register("piggyshopui", new ShopCommand($this, "shop", "Open the shop menu"));

        if (!isset($this->shopCategories["test"])) {
            $category = new ShopCategory("test", [], false);
            $this->shopCategories["test"] = $category;
            $category->addItem(new ShopItem(Item::get(Item::PORKCHOP, 0, 1)->setCustomName(TextFormat::RESET . "Mystical Porkchop"), "It's a porkchop", 1000, false, 0));
        }
    }

    public function onDisable(): void
    {
        $this->saveToShopConfig();
    }

    /**
     * @return PiggyShopUI
     */
    public static function getInstance(): PiggyShopUI
    {
        return self::$instance;
    }

    /**
     * @return Config
     */
    public function getShopConfig(): Config
    {
        return $this->shopConfig;
    }

    public function saveToShopConfig(): void
    {
        $this->shopConfig->setAll(array_map(function (ShopCategory $category): array {
            return $category->serialize();
        }, $this->shopCategories));
        $this->shopConfig->save();
    }

    /**
     * @param string $name
     * @return ShopCategory|null
     */
    public function getShopCategory(string $name): ?ShopCategory
    {
        return $this->shopCategories[$name] ?? null;
    }

    /**
     * @return ShopCategory[]
     */
    public function getShopCategories(): array
    {
        return $this->shopCategories;
    }

    /**
     * @return EconomyProvider
     */
    public function getEconomyProvider(): EconomyProvider
    {
        return $this->economyProvider;
    }
}