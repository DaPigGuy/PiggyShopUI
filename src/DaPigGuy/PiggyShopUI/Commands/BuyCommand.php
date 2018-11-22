<?php

namespace DaPigGuy\PiggyShopUI\Commands;

use DaPigGuy\PiggyShopUI\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class BuyCommand
 * @package DaPigGuy\PiggyShopUI\Commands
 */
class BuyCommand extends PluginCommand
{
    /**
     * BuyCommand constructor.
     * @param string $name
     * @param Main   $plugin
     */
    public function __construct(string $name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription("Access the shop UI");
        $this->setPermission("piggyshopui.command.buy");
        $this->setUsage("/buy [category]");
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     * @return bool|mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($sender instanceof Player) {
                if (isset($args[0])) {
                    if (isset($plugin->buyCategories[$args[0]])) {
                        $plugin->openBuyCategoryMenu($sender, $args[0]);
                        return;
                    }
                    $sender->sendMessage(TextFormat::RED . "Invalid category.");
                    return;
                }
                $plugin->openBuyMainMenu($sender);
                return;
            }
            $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
            return;
        }
    }
}