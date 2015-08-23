<?php
namespace InfluxDB\Adapter;

use DateTime;
use InfluxDB\Options;
use InfluxDB\Adapter\WritableInterface;

abstract class AdapterAbstract implements WritableInterface
{
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    private function getMessageDefaults()
    {
        return [
            "database" => $this->getOptions()->getDatabase(),
            "retentionPolicy" => $this->getOptions()->getRetentionPolicy(),
            "tags" => $this->getOptions()->getTags(),
        ];
    }

    abstract public function send(array $message);

    public function messageToLineProtocol(array $message)
    {
        if (!array_key_exists("points", $message)) {
            return;
        }

        $message = array_replace_recursive($this->getMessageDefaults(), $message);

        if (array_key_exists("tags", $message)) {
            $message["tags"] = array_replace_recursive($this->getOptions()->getTags(), $message["tags"]);
        }

        $unixepoch = (int)(microtime(true) * 1e9);
        if (array_key_exists("time", $message)) {
            $dt = new DateTime($message["time"]);
            $unixepoch = (int)($dt->format("U") * 1e9);
        }

        $lines = [];
        foreach ($message["points"] as $point) {
            $tags = array_key_exists("tags", $message) ? $message["tags"] : [];
            if (array_key_exists("tags", $point)) {
                $tags = array_replace_recursive($tags, $point["tags"]);
            }

            $tagLine = "";
            if ($tags) {
                $tagLine = sprintf(",%s", $this->listToString($tags));
            }

            $lines[] = sprintf(
                "%s%s %s %d", $point["measurement"], $tagLine, $this->listToString($point["fields"], true), $unixepoch
            );
        }

        return implode("\n", $lines);
    }

    public function listToString(array $elements, $escape = false)
    {
        $options = $this->getOptions();
        array_walk($elements, function(&$value, $key) use ($escape, $options) {
            if ($escape && is_string($value)) {
                $value = "\"{$value}\"";
            }

            if (is_bool($value)) {
                $value = ($value) ? "true" : "false";
            }

            if ($options->getForceIntegers() && is_int($value)) {
                $value = "{$value}i";
            }

            $value = "{$key}={$value}";
        });

        return implode(",", $elements);
    }
}
