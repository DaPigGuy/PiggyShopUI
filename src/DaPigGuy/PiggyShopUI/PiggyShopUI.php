<?php

namespace DaPigGuy\PiggyShopUI;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\PacketHooker;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use DaPigGuy\PiggyShopUI\commands\ShopCommand;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use DaPigGuy\PiggyShopUI\tasks\CheckUpdatesTask;
use DaPigGuy\PiggyShopUI\utils\Utils;
use jojoe77777\FormAPI\Form;
use JsonException;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class PiggyShopUI extends PluginBase
{
    public static PiggyShopUI $instance;

    private Config $messages;

    /** @var ShopCategory[] */
    public array $shopCategories = [];
    public Config $shopConfig;

    public EconomyProvider $economyProvider;

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
                $this->getLogger()->error($virion . " virion not found. Please download PiggyShopUI from Poggit-CI or use DEVirion (not recommended).");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
        }

        self::$instance = $this;

        Utils::init();

        $this->saveResource("messages.yml");
        $this->messages = new Config($this->getDataFolder() . "messages.yml");
        $this->saveDefaultConfig();

        libPiggyEconomy::init();
        $this->economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));

        $this->shopConfig = new Config($this->getDataFolder() . "shops.yml");
        foreach ($this->shopConfig->getAll() as $category) {
            $this->shopCategories[$category["name"]] = ShopCategory::deserialize($category);
        }

        if (!PacketHooker::isRegistered()) PacketHooker::register($this);
        $this->getServer()->getCommandMap()->register("piggyshopui", new ShopCommand($this, "shop", "Open the shop menu"));

        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdatesTask());
    }

    public static function getInstance(): PiggyShopUI
    {
        return self::$instance;
    }

    public function getMessage(string $key, array $tags = []): string
    {
        return Utils::translateColorTags(str_replace(array_keys($tags), $tags, $this->messages->getNested($key, $key)));
    }

    public function getEconomyProvider(): EconomyProvider
    {
        return $this->economyProvider;
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

    /**
     * @return ShopCategory[]
     */
    public function getShopCategories(): array
    {
        return $this->shopCategories;
    }

    public function getShopCategory(string $name): ?ShopCategory
    {
        return $this->shopCategories[$name] ?? null;
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

    public function getNameByDamage(Item $item): string
    {
        $types = [
            ItemIds::BANNER => ["Black", "Red", "Green", "Brown", "Blue", "Purple", "Cyan", "Light Gray", "Gray", "Pink", "Lime", "Yellow", "Light Blue", "Magenta", "Orange", "White", "Black", "Brown", "Blue", "White"],
            ItemIds::BUCKET => ["Bucket", "Milk", "Cod", "Salmon", "Tropical Fish", "Pufferfish", "", "", "Water", "", "Lava"],
            ItemIds::DYE => ["Black", "Red", "Green", "Brown", "Blue", "Purple", "Cyan", "Light Gray", "Gray", "Pink", "Lime", "Yellow", "Light Blue", "Magenta", "Orange", "White", "Black", "Brown", "Blue", "White"],
            ItemIds::TERRACOTTA => ["White", "Orange", "Magenta", "Light Blue", "Yellow", "Lime", "Pink", "Gray", "Light Gray", "Cyan", "Purple", "Blue", "Brown", "Green", "Red", "Black"]
        ];
        if (isset($types[$item->getId()][$item->getMeta()])) {
            $type = $types[$item->getId()][$item->getMeta()];
            switch ($item->getId()) {
                case ItemIds::BANNER:
                    return $type . " Banner";
                case ItemIds::BUCKET:
                    if ($item->getMeta() === 0) {
                        return $type;
                    } elseif ($item->getMeta() <= 5) {
                        return "Bucket of " . $type;
                    } elseif ($item->getMeta() === 8 || $item->getMeta() === 10) {
                        return $type . " Bucket";
                    }
                    break;
                case ItemIds::DYE:
                    return $item->getMeta() === 15 ? $type : $type . " Dye";
                case ItemIds::TERRACOTTA:
                    return $type . " Terracotta";
            }
        }

        $potions = ["Water", "Mundane", "Long Mundane", "Thick", "Awkward", "Night Vision", "Night Vision", "Invisibility", "Invisibility", "Leaping", "Leaping", "Leaping", "Fire Resistance", "Fire Resistance", "Swiftness", "Swiftness", "Swiftness", "Slowness", "Slowness", "Water Breathing", "Water Breathing", "Healing", "Healing", "Harming", "Harming", "Poison", "Poison", "Regeneration", "Regeneration", "Regeneration", "Strength", "Strength", "Strength", "Weakness", "Weakness", "Wither"];
        if ($item->getId() === ItemIds::POTION || $item->getId() === ItemIds::SPLASH_POTION) {
            if ($item->getMeta() <= 4) {
                return $potions[$item->getMeta()] . ($item->getId() === ItemIds::SPLASH_POTION ? " Splash" : "") . " Potion";
            } else {
                return ($item->getId() === ItemIds::SPLASH_POTION ? " Splash " : "") . "Potion of " . $potions[$item->getMeta()];
            }
        }

        return $item->getName();
    }
}