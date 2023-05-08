<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/BugsCommand.php';
require __DIR__ . '/Peasants.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Dotenv\Dotenv;
use Peasants\Peasant;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$stickyMessageContent = null;

$discord = new Discord([
    'token' => getenv('DISCORD_TOKEN'),
    'intents' => Intents::getDefaultIntents()
]);

$discord->on("ready", function (Discord $discord) use (&$stickyMessageContent) {
    echo "Bot is ready!", PHP_EOL;

    // Load sticky message from file
    $stickyMessageContent = file_get_contents('sticky');

    echo "Sticky message: $stickyMessageContent", PHP_EOL;
});

$discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use (&$stickyMessageContent) {
    if($message->channel_id != getenv('MAIN_CHANNEL_ID')) return; // If the message is not from the main channel, ignore it
    if($message->author->id == $discord->id) return; // If the message is from the bot, ignore it

    if (Peasant::isAdmin($message->author->id) && substr($message->content, 0, 8) == '!sticky ') { // If the message is from an admin and starts with !sticky
        $stickyMessageContent = substr($message->content, 8);

        echo "Sticky message changed to: $stickyMessageContent", PHP_EOL;

        // Delete the command message
        $message->delete();

        // Send the new sticky message
        $message->channel->sendMessage($stickyMessageContent);

        // Write to file
        file_put_contents('sticky', $stickyMessageContent);
    } else if(Peasant::isAdmin($message->author->id) && substr($message->content, 0, 7) == '!chave ') {
        $msg = explode(" ", $message->content);

        if(count($msg) >= 1) {
            $nick = $msg[1];

            $db = new mysqli(
                getenv('DB_HOST'), 
                getenv('DB_USER'), 
                getenv('DB_PASSWORD'), 
                getenv('DB_NAME')
            );

            // Sanitize $nick
            $nick   = $db->real_escape_string($nick);
            $result = $db->query("SELECT code FROM otp WHERE nick = '$nick';");

            if($result->num_rows) {
                $code = $result->fetch_assoc()['code'];
                $message->channel->sendMessage("A chave de acesso de `$nick` é `$code`");
            } else {
                $message->channel->sendMessage("Não foi possível encontrar uma chave para `$nick`");
            }

            $db->close();
        }
    } else if (substr($message->content, 0, 6) == '!bugs') {
        $bugs = BugsCommand::fetchBugs();
        $output = BugsCommand::displayBugs($bugs);
        $message->channel->sendMessage($output);
    } else if (Peasant::isAskingForIP($message->content)) {
        $serverIP = getenv('SERVER_ADDR'); // Replace with your actual server IP
        $message->channel->sendMessage("O IP do servidor é: `$serverIP`");
    } else if ($insult = Peasant::isSayingShit($message->content)) {
        $message->delete(); // Delete the message containing bad activity
    
        // Send the insult message mentioning the user
        $message->channel->sendMessage("{$message->author}, $insult");
    
        // Send a warning message to the staff channel with the user's name and content of the deleted message
        // Check if the bot has access to the staff channel
        $staffChannel = $discord->getChannel(getenv('STAFF_CHANNEL_ID'));
        if ($staffChannel !== null)
            $staffChannel->sendMessage("Usuário {$message->author} enviou uma mensagem inadequada: `{$message->content}`");
    } else if ($message->content !== $stickyMessageContent) { // If the message is not the sticky message and not from the bot
        // Delete the previous sticky message
        $message->channel->getMessageHistory(['limit' => 100])
            ->done(function ($messages) use ($message, $stickyMessageContent) {
                foreach ($messages as $msg) { // This shit doesn't read bot messages
                    if ($msg->content == $stickyMessageContent) {
                        $msg->delete();
                    }
                }

                // Send the new sticky message after the latest non-sticky message
                $message->channel->sendMessage($stickyMessageContent);
            });
    } 
});

$discord->run();
