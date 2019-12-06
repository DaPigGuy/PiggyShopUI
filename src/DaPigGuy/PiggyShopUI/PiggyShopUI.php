<?php

namespace DaPigGuy\PiggyShopUI;

use CortexPE\Commando\BaseCommand;
use DaPigGuy\libPiggyEconomy\exceptions\MissingProviderDependencyException;
use DaPigGuy\libPiggyEconomy\exceptions\UnknownProviderException;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use DaPigGuy\PiggyShopUI\commands\ShopCommand;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use DaPigGuy\PiggyShopUI\shops\ShopItem;
use DaPigGuy\PiggyShopUI\tasks\CheckUpdatesTask;
use jojoe77777\FormAPI\Form;
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

    /**
     * @throws MissingProviderDependencyException
     * @throws UnknownProviderException
     */
    public function onEnable(): void
    {
        if (!class_exists(BaseCommand::class)) {
            $this->getLogger()->error("Commando virion not found. Please download PiggyShopUI from Poggit-CI or use DEVirion (not recommended).");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        if (!class_exists(Form::class)) {
            $this->getLogger()->error("libformapi virion not found. Please download PiggyShopUI from Poggit-CI or use DEVirion (not recommended).");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        if (!class_exists(libPiggyEconomy::class)) {
            $this->getLogger()->error("libPiggyEconomy virion not found. Please download PiggyShopUI from Poggit-CI or use DEVirion (not recommended).");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        self::$instance = $this;

        $this->saveDefaultConfig();

        libPiggyEconomy::init();
        $this->economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));

        $this->shopConfig = new Config($this->getDataFolder() . "shops.yml");
        foreach ($this->shopConfig->getAll() as $category) {
            $this->shopCategories[$category["name"]] = new ShopCategory($category["name"], array_map(function (array $item): ShopItem {
                return new ShopItem(Item::jsonDeserialize($item["item"]), $item["description"], $item["buyPrice"], $item["canSell"], $item["sellPrice"], $item["imageType"] ?? -1, $item["imagePath"] ?? "");
            }, $category["items"]), $category["private"], $category["imageType"] ?? -1, $category["imagePath"] ?? "");
        }

        $this->getServer()->getCommandMap()->register("piggyshopui", new ShopCommand($this, "shop", "Open the shop menu"));

        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdatesTask($this->getDescription()->getVersion(), $this->getDescription()->getCompatibleApis()[0]));
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