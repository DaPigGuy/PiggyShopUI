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
                return new ShopItem(Item::jsonDeserialize($item["item"]), $item["description"], $item["buyPrice"], $item["canSell"], $item["sellPrice"], $item["imageType"] ?? -1, $item["imagePath"] ?? "");
            }, $category["items"]), $category["private"], $category["imageType"] ?? -1, $category["imagePath"] ?? "");
        }

        $this->getServer()->getCommandMap()->register("piggyshopui", new ShopCommand($this, "shop", "Open the shop menu"));
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
     * @param ShopCategory $category
     */
    public function addShopCategory(ShopCategory $category): void
    {
        $this->shopCategories[$category->getName()] = $category;
        $this->saveToShopConfig();
    }

    /**
     * @param ShopCategory $category
     */
    public function removeShopCategory(ShopCategory $category): void
    {
        unset($this->shopCategories[$category->getName()]);
        $this->saveToShopConfig();
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