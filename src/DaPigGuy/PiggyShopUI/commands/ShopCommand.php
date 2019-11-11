<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\commands;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use DaPigGuy\PiggyShopUI\commands\enum\ShopCategoryEnum;
use DaPigGuy\PiggyShopUI\PiggyShopUI;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use DaPigGuy\PiggyShopUI\shops\ShopItem;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class ShopCommand
 * @package DaPigGuy\PiggyShopUI\commands
 */
class ShopCommand extends BaseCommand
{
    /** @var PiggyShopUI */
    private $plugin;

    /**
     * @param string $name
     * @param string $description
     * @param string $usageMessage
     * @param string[] $aliases
     */
    public function __construct(PiggyShopUI $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param BaseArgument[] $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
            return;
        }
        if (isset($args["category"])) {
            if ($args["category"] === null) {
                $sender->sendMessage(TextFormat::RED . "Invalid shop category.");
                return;
            }
            $this->showCategoryItems($sender, $args["category"]);
            return;
        }
        $categories = $this->plugin->getShopCategories();
        if (count($categories) === 0) {
            $sender->sendMessage(TextFormat::RED . "No existing shop categories exist.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($categories) {
            if ($data !== null) {
                $this->showCategoryItems($player, $categories[array_keys($categories)[$data]]);
            }
        });
        $form->setTitle($this->plugin->getConfig()->getNested("messages.menu.main-title"));
        foreach ($categories as $category) {
            $form->addButton(str_replace("{CATEGORY}", $category->getName(), $this->plugin->getConfig()->getNested("messages.menu.category-button")));
        }
        $sender->sendForm($form);
    }

    /**
     * @param Player $player
     * @param ShopCategory $category
     */
    public function showCategoryItems(Player $player, ShopCategory $category): void
    {
        $items = $category->getItems();
        if (count($items) === 0) {
            $player->sendMessage(TextFormat::RED . "No existing items exist within this category.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($items) {
            if ($data !== null) {
                $this->showItemPage($player, $items[array_keys($items)[$data]]);
            }
        });
        $form->setTitle(str_replace("{CATEGORY}", $category->getName(), $this->plugin->getConfig()->getNested("messages.menu.category-page-title")));
        foreach ($items as $item) {
            $form->addButton(str_replace(["{COUNT}", "{ITEM}"], [$item->getItem()->getCount(), $item->getItem()->getName()], $this->plugin->getConfig()->getNested("messages.menu.item-button")));
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param ShopItem $item
     */
    public function showItemPage(Player $player, ShopItem $item): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($item) {
            if ($data !== null) {
            }
        });
        $form->setTitle(str_replace(["{COUNT}", "{ITEM}"], [$item->getItem()->getCount(), $item->getItem()->getName()], $this->plugin->getConfig()->getNested("messages.menu.item-page-title")));
        $form->addLabel(
            (empty($item->getDescription()) ? "" : $item->getDescription() . "\n\n") .
            (str_replace("{PRICE}", $item->getBuyPrice(), $this->plugin->getConfig()->getNested("messages.menu.item-purchase-price"))) .
            ($item->canSell() ? (str_replace("{PRICE}", $item->getSellPrice(), $this->plugin->getConfig()->getNested("messages.menu.item-sell-price"))) : "")
        );
        $form->addInput("Amount");
        if($item->canSell()) $form->addToggle("Sell", false);
        $player->sendForm($form);
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new ShopCategoryEnum("category", true));
    }
}