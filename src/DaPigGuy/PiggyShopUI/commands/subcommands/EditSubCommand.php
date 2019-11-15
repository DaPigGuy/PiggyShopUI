<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyShopUI\PiggyShopUI;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use DaPigGuy\PiggyShopUI\shops\ShopItem;
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
                        $this->showEditCategoriesPage($player);
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
                if (strtolower($data[0]) === "edit") {
                    $player->sendMessage(TextFormat::RED . "'" . $data[0] . "' is an invalid shop category name.");
                    return;
                }
                $this->plugin->addShopCategory(new ShopCategory($data[0], [], $data[1], $data[2] - 1, $data[3]));
                $player->sendMessage(TextFormat::GREEN . "Shop category " . $data[0] . " created successfully.");
            }
        });
        $form->setTitle("Add Shop Category");
        $form->addInput("Name");
        $form->addToggle("Private", false);
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"]);
        $form->addInput("Image Path/URL", "");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     */
    public function showEditCategoriesPage(Player $player): void
    {
        $categories = $this->plugin->getShopCategories();
        $form = new SimpleForm(function (Player $player, ?int $data) use ($categories): void {
            if ($data !== null) {
                $this->showEditCategoryPage($player, $categories[array_keys($categories)[$data]]);
            }
        });
        $form->setTitle("Edit Shop Categories");
        foreach ($categories as $category) {
            $form->addButton($category->getName());
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param ShopCategory $category
     */
    public function showEditCategoryPage(Player $player, ShopCategory $category): void
    {
        $form = new SimpleForm(function (Player $player, ?int $data) use ($category): void {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        $this->showAddCategoryItemPage($player, $category);
                        break;
                    case 1:
                        $this->showEditCategoryItemsPage($player, $category);
                        break;
                    case 2:
                        $this->showRemoveCategoryItemPage($player, $category);
                        break;
                    case 3:
                        $this->showEditCategorySettingsPage($player, $category);
                        break;
                }
            }
        });
        $form->setTitle("Edit '" . $category->getName() . "' Category");
        $form->addButton("Add Item");
        $form->addButton("Edit Item");
        $form->addButton("Remove Item");
        $form->addButton("Edit Settings");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param ShopCategory $category
     */
    public function showAddCategoryItemPage(Player $player, ShopCategory $category): void
    {
        $items = array_values($player->getInventory()->getContents());
        $form = new CustomForm(function (Player $player, ?array $data) use ($items, $category): void {
            if ($data !== null) {
                if (!is_numeric($data[3]) || !is_numeric($data[6])) {
                    $player->sendMessage(TextFormat::RED . "Prices must be numeric.");
                    return;
                }
                $shopItem = new ShopItem($items[$data[0]], $data[2], (int)$data[3], $data[4], (int)$data[6] ?? 0, $data[7] - 1, $data[8]);
                $category->addItem($shopItem);
                $player->sendMessage(TextFormat::GREEN . "Item successfully added.");
            }
        });
        $form->setTitle("Add '" . $category->getName() . "'Category Item");
        $form->addDropdown("Item", array_map(function (Item $item): string {
            return $item->getName();
        }, $items));
        $form->addLabel("Item descriptions are optional.");
        $form->addInput("Description");
        $form->addInput("Buy Price");
        $form->addToggle("Can Sell", false);
        $form->addLabel("Do not change 'Sell Price' if 'Can Sell' is disabled.");
        $form->addInput("Sell Price", "", "0");
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"]);
        $form->addInput("Image Path/URL", "");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param ShopCategory $category
     */
    public function showEditCategoryItemsPage(Player $player, ShopCategory $category): void
    {
        $items = $category->getItems();
        if (count($items) === 0) {
            $player->sendMessage(TextFormat::RED . "No items exist within this category.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($items): void {
            if ($data !== null) {
                $this->showEditCategoryItemPage($player, $items[array_keys($items)[$data]]);
            }
        });
        $form->setTitle("Edit '" . $category->getName() . "' Category Items");
        foreach ($items as $item) {
            $form->addButton($item->getItem()->getName());
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param ShopItem $item
     */
    public function showEditCategoryItemPage(Player $player, ShopItem $item): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($item): void {
            if ($data !== null) {
                if (!is_numeric($data[1]) || !is_numeric($data[3])) {
                    $player->sendMessage(TextFormat::RED . "Prices must be numeric.");
                    return;
                }
                $item->setDescription($data[0]);
                $item->setBuyPrice((int)$data[1]);
                $item->setCanSell($data[2]);
                $item->setSellPrice((int)$data[3]);
                $item->setImageType($data[4] - 1);
                $item->setImagePath($data[5]);
                $player->sendMessage(TextFormat::GREEN . "Item updated successfully.");
            }
        });
        $form->setTitle("Edit Item '" . $item->getItem()->getName() . "'");
        $form->addInput("Description", "", $item->getDescription());
        $form->addInput("Buy Price", "", (string)$item->getBuyPrice());
        $form->addToggle("Can Sell", $item->canSell());
        $form->addInput("Sell Price", "", (string)$item->getSellPrice());
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"], $item->getImageType() + 1);
        $form->addInput("Image Path/URL", "", $item->getImagePath());
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param ShopCategory $category
     */
    public function showRemoveCategoryItemPage(Player $player, ShopCategory $category): void
    {
        $items = array_values($category->getItems());
        $form = new CustomForm(function (Player $player, ?array $data) use ($category): void {
            if ($data !== null) {
                $category->removeItem($data[0]);
                $player->sendMessage(TextFormat::GREEN . "Item successfully removed.");
            }
        });
        $form->setTitle("Remove '" . $category->getName() . "' Category Item");
        $form->addDropdown("Item", array_map(function (ShopItem $item): string {
            return $item->getItem()->getName();
        }, $items));
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param ShopCategory $category
     */
    public function showEditCategorySettingsPage(Player $player, ShopCategory $category): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($category): void {
            if ($data !== null) {
                if ($category->getName() !== $data[0]) {
                    if ($this->plugin->getShopCategory($data[0]) !== null) {
                        $player->sendMessage(TextFormat::RED . "Could not rename. A shop category already exists with the name.");
                    } else {
                        $category->setName($data[0]);
                        $player->sendMessage(TextFormat::GREEN . "Category renamed to '" . $category->getName() . "'");
                    }
                }
                if ($category->isPrivate() !== $data[1]) {
                    $player->sendMessage(($data[1] ? TextFormat::GREEN : TextFormat::RED) . "Category is no" . ($data[1] ? "w private." : " longer private."));
                }
                $category->setPrivate($data[1]);
                if ($category->getImageType() !== $data[2] - 1 || $category->getImagePath() !== $data[3]) {
                    $player->sendMessage(TextFormat::GREEN . "Category image updated.");
                }
                $category->setImageType($data[2] - 1);
                $category->setImagePath($data[3]);
            }
        });
        $form->setTitle("'" . $category->getName() . "' Category Settings");
        $form->addInput("Name", "", $category->getName());
        $form->addToggle("Private", $category->isPrivate());
        $form->addDropdown("Image Type", ["Disabled", "Path", "URL"], $category->getImageType() + 1);
        $form->addInput("Image Path/URL", "", $category->getImagePath());
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