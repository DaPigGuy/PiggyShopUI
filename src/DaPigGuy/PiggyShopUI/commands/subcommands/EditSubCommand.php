<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyShopUI\PiggyShopUI;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class EditSubCommand
 * @package DaPigGuy\PiggyShopUI\commands\subcommands
 */
class EditSubCommand extends BaseSubCommand
{
    /** @var PiggyShopUI */
    private $plugin;

    /**
     * EditSubCommand constructor.
     * @param PiggyShopUI $plugin
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(PiggyShopUI $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        $this->showAddCategoryPage($player);
                        break;
                    case 1:
                        break;
                    case 2:
                        $this->showRemoveCategoryPage($player);
                        break;
                }
            }
        });
        $form->setTitle("Manage Shop Categories");
        $form->addButton("Add Category");
        $form->addButton("Edit Category");
        $form->addButton("Remove Category");
        $sender->sendForm($form);
    }

    /**
     * @param Player $player
     */
    public function showAddCategoryPage(Player $player): void
    {
        $items = array_values($player->getInventory()->getContents());
        $form = new CustomForm(function (Player $player, ?array $data) use ($items): void {
            if ($data !== null) {
                if ($this->plugin->getShopCategory($data[0]) !== null) {
                    $player->sendMessage(TextFormat::RED . "A shop category already exists with the name " . $data[0] . ".");
                    return;
                }
                $this->plugin->addShopCategory(new ShopCategory($data[0], $data[2] === 0 ? [] : [$items[$data[2]]], $data[3]));
                $player->sendMessage(TextFormat::GREEN . "Shop category " . $data[0] . " created successfully.");
            }
        });
        $form->setTitle("Add Shop Category");
        $form->addInput("Name");
        $form->addLabel("Adding an item now is optional. It can be done later.");
        $form->addDropdown("Item", array_merge(["None"], array_map(function (Item $item): string {
            return $item->getName();
        }, $items)));
        $form->addToggle("Private", false);
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     */
    public function showRemoveCategoryPage(Player $player): void
    {
        /** @var ShopCategory[] $categories */
        $categories = array_values($this->plugin->getShopCategories());
        $form = new CustomForm(function (Player $player, ?array $data) use ($categories): void {
            if ($data !== null) {
                $this->plugin->removeShopCategory($categories[$data[0]]);
                $player->sendMessage(TextFormat::GREEN . "Shop category " . $categories[$data[0]]->getName() . " removed successfully.");
            }
        });
        $form->setTitle("Remove Shop Category");
        $form->addDropdown("Category", array_map(function (ShopCategory $category): string {
            return $category->getName();
        }, $categories));
        $player->sendForm($form);

    }

    protected function prepare(): void
    {
        $this->setPermission("piggyshopui.command.shop.edit");
    }
}