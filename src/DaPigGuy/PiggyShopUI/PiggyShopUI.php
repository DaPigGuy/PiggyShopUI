<?php

namespace DaPigGuy\PiggyShopUI;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
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
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void
    {
        foreach (
            [
                "libPiggyEconomy" => libPiggyEconomy::class,
                "Commando" => BaseCommand::class,
                "libformapi" => Form::class
            ] as $virion => $class
        ) {
            if (!class_exists($class)) {
                $this->getLogger()->error($virion . " virion not found. Please download PiggyCustomEnchantsShop from Poggit-CI or use DEVirion (not recommended).");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
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

        if (!PacketHooker::isRegistered()) PacketHooker::register($this);
        $this->getServer()->getCommandMap()->register("piggyshopui", new ShopCommand($this, "shop", "Open the shop menu"));

        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdatesTask($this->getDescription()->getVersion(), $this->getDescription()->getCompatibleApis()[0]));
    }

    public static function getInstance(): PiggyShopUI
    {
        return self::$instance;
    }

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

    public function addShopCategory(ShopCategory $category): void
    {
        $this->shopCategories[$category->getName()] = $category;
        $this->saveToShopConfig();
    }

    public function removeShopCategory(ShopCategory $category): void
    {
        unset($this->shopCategories[$category->getName()]);
        $this->saveToShopConfig();
    }

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

    public function getEconomyProvider(): EconomyProvider
    {
        return $this->economyProvider;
    }

    public function getNameByDamage(int $itemId, int $damage): string
    {
        if ($itemId === Item::BUCKET) {
            $name = ["Bucket", "Milk", "Cod", "Salmon", "Tropical Fish", "Pufferfish", "", "", "Water", "", "Lava"];
            return $name[$damage];
        }
        if ($itemId === Item::DYE) {
            $name = ["Black", "Red", "Green", "Brown", "Blue", "Purple", "Cyan", "Light Gray", "Gray", "Pink", "Lime", "Yellow", "Light Blue", "Magenta", "Orange", "White", "Black", "Brown", "Blue", "White"];
            return $name[$damage];
        }
        if ($itemId === Item::POTION || $itemId === Item::SPLASH_POTION) {
            $name = ["Water", "Mundane", "Long Mundane", "Thick", "Awkward", "Night Vision", "Night Vision", "Invisibility", "Invisibility", "Leaping", "Leaping", "Leaping", "Fire Resistance", "Fire Resistance", "Swiftness", "Swiftness", "Swiftness", "Slowness", "Slowness", "Water Breathing", "Water Breathing", "Healing", "Healing", "Harming", "Harming", "Poison", "Poison", "Regeneration", "Regeneration", "Regeneration", "Strength", "Strength", "Strength", "Weakness", "Weakness", "Wither"];
            return $name[$damage];
        }
        if ($itemId === Item::TERRACOTTA) {
            $name = ["White", "Orange", "Magenta", "Light Blue", "Yellow", "Lime", "Pink", "Gray", "Light Gray", "Cyan", "Purple", "Blue", "Brown", "Green", "Red", "Black"];
            return $name[$damage];
        }
        return "";
    }
}