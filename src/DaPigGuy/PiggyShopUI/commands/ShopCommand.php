<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\commands;

use CortexPE\Commando\BaseCommand;
use DaPigGuy\PiggyShopUI\commands\enum\ShopCategoryEnum;
use DaPigGuy\PiggyShopUI\commands\subcommands\EditSubCommand;
use DaPigGuy\PiggyShopUI\PiggyShopUI;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use DaPigGuy\PiggyShopUI\shops\ShopItem;
use DaPigGuy\PiggyShopUI\shops\ShopSubcategory;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class ShopCommand extends BaseCommand implements PluginIdentifiableCommand
{
    /** @var PiggyShopUI */
    private $plugin;

    /**
     * @param PiggyShopUI $plugin
     * @param string $name
     * @param string $description
     * @param string[] $aliases
     */
    public function __construct(PiggyShopUI $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
            return;
        }
        if (isset($args["category"])) {
            if (!$args["category"] instanceof ShopCategory) {
                $sender->sendMessage(TextFormat::RED . "Invalid shop category.");
                return;
            }
            if ($args["category"]->isPrivate() && !$sender->hasPermission("piggyshopui.category." . strtolower($args["category"]->getName()))) {
                $sender->sendMessage(TextFormat::RED . "You do not have permission to view this category.");
                return;
            }
            $this->showCategoryItems($sender, $args["category"]);
            return;
        }
        $this->showCategories($sender);
    }

    public function showCategories(Player $player): void
    {
        /** @var ShopCategory[] $categories */
        $categories = array_filter($this->plugin->getShopCategories(), function (ShopCategory $category) use ($player): bool {
            return !$category->isPrivate() || $player->hasPermission("piggyshopui.category." . strtolower($category->getName()));
        });
        if (count($categories) === 0) {
            $player->sendMessage(TextFormat::RED . "No existing shop categories exist.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($categories): void {
            if ($data !== null) {
                $this->showCategoryItems($player, $categories[array_keys($categories)[$data]]);
            }
        });
        $form->setTitle($this->plugin->getConfig()->getNested("messages.menu.main-title"));
        foreach ($categories as $category) {
            $form->addButton(str_replace("{CATEGORY}", $category->getName(), $this->plugin->getConfig()->getNested("messages.menu.category-button")), $category->getImageType(), $category->getImagePath());
        }
        $player->sendForm($form);
    }

    public function showCategoryItems(Player $player, ShopCategory $category): void
    {
        $entries = array_merge($category->getSubCategories(), $category->getItems());
        if (count($entries) === 0) {
            $player->sendMessage(TextFormat::RED . "No items or subcategories exist within this category.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($category, $entries): void {
            if ($data !== null) {
                if ($data === count($entries)) {
                    if ($category instanceof ShopSubcategory) {
                        $this->showCategoryItems($player, $category->getParent());
                        return;
                    }
                    $this->showCategories($player);
                    return;
                }
                $entry = $entries[array_keys($entries)[$data]];
                if ($entry instanceof ShopItem) {
                    $this->showItemPage($player, $category, $entry);
                    return;
                }
                $this->showCategoryItems($player, $entry);
            }
        });
        $form->setTitle(str_replace("{CATEGORY}", $category->getName(), $this->plugin->getConfig()->getNested("messages.menu.category-page-title")));
        foreach ($category->getSubCategories() as $subcategory) {
            $form->addButton(str_replace("{SUBCATEGORY}", $subcategory->getName(), $this->plugin->getConfig()->getNested("messages.menu.subcategory-button")), $subcategory->getImageType(), $subcategory->getImagePath());
        }
        foreach ($category->getItems() as $item) {
            $name = $item->getItem()->hasCustomName() ? $item->getItem()->getName() : $this->plugin->getNameByDamage($item->getItem());
            $form->addButton(str_replace(["{COUNT}", "{ITEM}", "{BUYPRICE}", "{SELLPRICE}"], [$item->getItem()->getCount(), $name, $item->getBuyPrice(), $item->getSellPrice()], $this->plugin->getConfig()->getNested("messages.menu.item-button")), $item->getImageType(), $item->getImagePath());
        }
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function showItemPage(Player $player, ShopCategory $category, ShopItem $item): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($category, $item): void {
            if ($data !== null) {
                if (!is_numeric($data[1]) || (int)$data[1] < 0) {
                    $player->sendMessage(TextFormat::RED . "Amount must be numeric.");
                    return;
                }
                if (!$item->canSell() || !$data[2]) {
                    if ($this->plugin->getEconomyProvider()->getMoney($player) < $item->getBuyPrice() * (int)$data[1]) {
                        $player->sendMessage(str_replace(["{PRICE}", "{DIFFERENCE}"], [$item->getBuyPrice() * (int)$data[1], $item->getBuyPrice() * (int)$data[1] - $this->plugin->getEconomyProvider()->getMoney($player)], $this->plugin->getConfig()->getNested("messages.buy.not-enough-money")));
                        return;
                    }
                    $purchasedItem = clone $item->getItem();
                    $purchasedItem->setCount($purchasedItem->getCount() * (int)$data[1]);
                    if (!$player->getInventory()->canAddItem($purchasedItem)) {
                        $player->sendMessage($this->plugin->getConfig()->getNested("messages.buy.not-enough-space"));
                        return;
                    }
                    $player->getInventory()->addItem($purchasedItem);
                    $this->plugin->getEconomyProvider()->takeMoney($player, $item->getBuyPrice() * (int)$data[1]);
                    $player->sendMessage(str_replace(["{COUNT}", "{ITEM}", "{PRICE}"], [$purchasedItem->getCount(), $purchasedItem->getName(), $item->getBuyPrice() * (int)$data[1]], $this->plugin->getConfig()->getNested("messages.buy.buy-success")));
                } else {
                    $offeredItems = clone $item->getItem();
                    $offeredItems->setCount($offeredItems->getCount() * (int)$data[1]);
                    if (!$player->getInventory()->contains($offeredItems)) {
                        $total = 0;
                        /** @var Item $i */
                        foreach ($player->getInventory()->all($offeredItems) as $i) {
                            $total += $i->getCount();
                        }
                        $player->sendMessage(str_replace(["{COUNT}", "{ITEM}", "{DIFFERENCE}"], [$offeredItems->getCount(), $offeredItems->getName(), $offeredItems->getCount() - $total], $this->plugin->getConfig()->getNested("messages.sell.not-enough-items")));
                        return;
                    }
                    $player->getInventory()->removeItem($offeredItems);
                    $this->plugin->getEconomyProvider()->giveMoney($player, $item->getSellPrice() * (int)$data[1]);
                    $player->sendMessage(str_replace(["{COUNT}", "{ITEM}", "{PRICE}"], [$offeredItems->getCount(), $offeredItems->getName(), $item->getSellPrice() * (int)$data[1]], $this->plugin->getConfig()->getNested("messages.sell.sell-success")));
                }
            }
            $this->showCategoryItems($player, $category);
        });
        $form->setTitle(str_replace(["{COUNT}", "{ITEM}"], [$item->getItem()->getCount(), $item->getItem()->getName()], $this->plugin->getConfig()->getNested("messages.menu.item-page-title")));
        $form->addLabel(
            (empty($item->getDescription()) ? "" : $item->getDescription() . "\n\n") .
            (str_replace(["{BALANCE}", "{OWNED}"], [$this->plugin->getEconomyProvider()->getMoney($player), array_sum(array_map(function (Item $item): int {
                return $item->getCount();
            }, $player->getInventory()->all($item->getItem())))], $this->plugin->getConfig()->getNested("messages.menu.player-info", ""))) . "\n" .
            (str_replace("{PRICE}", (string)$item->getBuyPrice(), $this->plugin->getConfig()->getNested("messages.menu.item-purchase-price"))) . "\n" .
            ($item->canSell() ? (str_replace("{PRICE}", (string)$item->getSellPrice(), $this->plugin->getConfig()->getNested("messages.menu.item-sell-price"))) : "")
        );
        $form->addInput("Amount");
        if ($item->canSell()) $form->addToggle("Sell", false);
        $player->sendForm($form);
    }

    protected function prepare(): void
    {
        $this->setPermission("piggyshopui.command.shop.use");
        $this->registerSubCommand(new EditSubCommand($this->plugin, "edit", "Edit shop categories"));
        $this->registerArgument(0, new ShopCategoryEnum("category", true));
    }
}