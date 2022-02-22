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
use pocketmine\item\Item;
use pocketmine\player\Player;

class ShopCommand extends BaseCommand
{
    /** @var PiggyShopUI */
    protected $plugin;

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage("command.use-in-game"));
            return;
        }
        if (isset($args["category"])) {
            if (!$args["category"] instanceof ShopCategory) {
                $sender->sendMessage($this->plugin->getMessage("menu.category.invalid"));
                return;
            }
            if ($args["category"]->isPrivate() && !$sender->hasPermission("piggyshopui.category." . strtolower($args["category"]->getName()))) {
                $sender->sendMessage($this->plugin->getMessage("menu.category.no-permission"));
                return;
            }
            $this->showCategoryItems($sender, $args["category"]);
            return;
        }
        $this->showCategories($sender);
    }

    public function showCategories(Player $player): void
    {
        $categories = array_filter($this->plugin->getShopCategories(), function (ShopCategory $category) use ($player): bool {
            return !$category->isPrivate() || $player->hasPermission("piggyshopui.category." . strtolower($category->getName()));
        });
        if (count($categories) === 0) {
            $player->sendMessage($this->plugin->getMessage("menu.category.no-categories"));
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($categories): void {
            if ($data !== null) {
                $this->showCategoryItems($player, $categories[array_keys($categories)[$data]]);
            }
        });
        $form->setTitle($this->plugin->getMessage("menu.main-title"));
        foreach ($categories as $category) {
            $form->addButton($this->plugin->getMessage("menu.category.button", ["{CATEGORY}" => $category->getName()]), $category->getImageType(), $category->getImagePath());
        }
        $player->sendForm($form);
    }

    public function showCategoryItems(Player $player, ShopCategory $category): void
    {
        $entries = array_merge($category->getSubCategories(), $category->getItems());
        if (count($entries) === 0) {
            $player->sendMessage($this->plugin->getMessage("menu.category.no-items"));
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
        $form->setTitle($this->plugin->getMessage("menu.category.page-title", ["{CATEGORY}" => $category->getName()]));
        foreach ($category->getSubCategories() as $subcategory) {
            $form->addButton($this->plugin->getMessage("menu.subcategory.button", ["{SUBCATEGORY}" => $subcategory->getName()]), $subcategory->getImageType(), $subcategory->getImagePath());
        }
        foreach ($category->getItems() as $item) {
            $name = $item->getItem()->hasCustomName() ? $item->getItem()->getName() : $this->plugin->getNameByDamage($item->getItem());
            $form->addButton($this->plugin->getMessage("menu.item.button", ["{COUNT}" => $item->getItem()->getCount(), "{ITEM}" => $name, "{BUYPRICE}" => $item->getBuyPrice(), "{SELLPRICE}" => $item->getSellPrice()]), $item->getImageType(), $item->getImagePath());
        }
        $form->addButton($this->plugin->getMessage("menu.back-button"));
        $player->sendForm($form);
    }

    public function showItemPage(Player $player, ShopCategory $category, ShopItem $item): void
    {
        $this->plugin->getEconomyProvider()->getMoney($player, function (float|int $amount) use ($player, $category, $item): void {
            $form = new CustomForm(function (Player $player, ?array $data) use ($category, $item): void {
                if ($data !== null) {
                    if (!is_numeric($data[1]) || (int)$data[1] < 0) {
                        $player->sendMessage($this->plugin->getMessage("menu.item.numeric"));
                        return;
                    }
                    if (!$item->canSell() || !$data[2]) {
                        $this->plugin->getEconomyProvider()->getMoney($player, function (float|int $amount) use ($player, $data, $item): void {
                            if ($amount < $item->getBuyPrice() * (int)$data[1]) {
                                $player->sendMessage($this->plugin->getMessage("buy.not-enough-money", ["{PRICE}" => $item->getBuyPrice() * (int)$data[1], "{DIFFERENCE}" => $item->getBuyPrice() * (int)$data[1] - $amount]));
                                return;
                            }
                            $purchasedItem = clone $item->getItem();
                            $purchasedItem->setCount($purchasedItem->getCount() * (int)$data[1]);
                            if (!$player->getInventory()->canAddItem($purchasedItem)) {
                                $player->sendMessage($this->plugin->getMessage("buy.not-enough-space"));
                                return;
                            }
                            $this->plugin->getEconomyProvider()->takeMoney($player, $item->getBuyPrice() * (int)$data[1], function (bool $success) use ($data, $item, $purchasedItem, $player): void {
                                if (!$success) {
                                    $player->sendMessage($this->plugin->getMessage("generic-error"));
                                    return;
                                }
                                $player->getInventory()->addItem($purchasedItem);
                                $player->sendMessage($this->plugin->getMessage("buy.buy-success", ["{COUNT}" => $purchasedItem->getCount(), "{ITEM}" => $purchasedItem->getName(), "{PRICE}" => $item->getBuyPrice() * (int)$data[1]]));
                            });
                        });
                    } else {
                        $offeredItems = clone $item->getItem();
                        $offeredItems->setCount($offeredItems->getCount() * (int)$data[1]);
                        if (!$player->getInventory()->contains($offeredItems)) {
                            $total = 0;
                            /** @var Item $i */
                            foreach ($player->getInventory()->all($offeredItems) as $i) {
                                $total += $i->getCount();
                            }
                            $player->sendMessage($this->plugin->getMessage("sell.not-enough-items", ["{COUNT}" => $offeredItems->getCount(), "{ITEM}" => $offeredItems->getName(), "{DIFFERENCE}" => $offeredItems->getCount() - $total]));
                            return;
                        }
                        $this->plugin->getEconomyProvider()->giveMoney($player, $item->getSellPrice() * (int)$data[1], function (bool $success) use ($data, $item, $offeredItems, $player): void {
                            if (!$success) {
                                $player->sendMessage($this->plugin->getMessage("generic-error"));
                                return;
                            }
                            $player->getInventory()->removeItem($offeredItems);
                            $player->sendMessage($this->plugin->getMessage("sell.sell-success", ["{COUNT}" => $offeredItems->getCount(), "{ITEM}" => $offeredItems->getName(), "{PRICE}" => $item->getSellPrice() * (int)$data[1]]));
                        });
                    }
                }
                $this->showCategoryItems($player, $category);
            });
            $form->setTitle($this->plugin->getMessage("menu.item.page-title", ["{COUNT}" => $item->getItem()->getCount(), "{ITEM}" => $item->getItem()->getName()]));
            $form->addLabel(
                (empty($item->getDescription()) ? "" : $item->getDescription() . "\n\n") .
                ($this->plugin->getMessage("menu.player-info", ["{BALANCE}" => $amount, "{OWNED}" => array_sum(array_map(function (Item $item): int {
                    return $item->getCount();
                }, $player->getInventory()->all($item->getItem())))])) . "\n" .
                ($this->plugin->getMessage("menu.item.purchase-price", ["{PRICE}" => (string)$item->getBuyPrice()]) . "\n" .
                    ($item->canSell() ? ($this->plugin->getMessage("menu.item.sell-price", ["{PRICE}" => (string)$item->getSellPrice()])) : ""))
            );
            $form->addInput("Amount");
            if ($item->canSell()) $form->addToggle("Sell", false);
            $player->sendForm($form);
        });
    }

    protected function prepare(): void
    {
        $this->setPermission("piggyshopui.command.shop.use");
        $this->registerSubCommand(new EditSubCommand($this->plugin, "edit", "Edit shop categories"));
        $this->registerArgument(0, new ShopCategoryEnum("category", true));
    }
}