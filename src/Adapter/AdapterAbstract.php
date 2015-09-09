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

    abstract public function send(array $message);

    protected function messageToLineProtocol(array $message)
    {
        if (!array_key_exists("points", $message)) {
            return;
        }

        if (!array_key_exists("tags", $message)) {
            $message["tags"] = [];
        }

        $message["tags"] = array_replace_recursive($this->getOptions()->getTags(), $message["tags"]);

        $unixepoch = (int)(microtime(true) * 1e9);
        if (array_key_exists("time", $message)) {
            $dt = new DateTime($message["time"]);
            $unixepoch = (int)($dt->format("U") * 1e9);
        }

        $lines = [];
        foreach ($message["points"] as $point) {
            $tags = $message["tags"];
            if (array_key_exists("tags", $point)) {
                $tags = array_replace_recursive($tags, $point["tags"]);
            }

            $tagLine = $this->tagsToString($tags);

            $lines[] = sprintf(
                "%s%s %s %d", $point["measurement"], $tagLine, $this->pointsToString($point["fields"]), $unixepoch
            );
        }

        return implode("\n", $lines);
    }

    protected function tagsToString(array $tags)
    {
        $tagLine = "";
        if (count($tags) > 0) {
            array_walk($tags, function(&$value, $key) {
                $value = "{$key}={$value}";
            });
            $tagLine = sprintf(",%s", implode(",", $tags));
        }

        return $tagLine;
    }

    protected function pointsToString(array $elements)
    {
        array_walk($elements, function(&$value, $key) {
            $dataType = gettype($value);
            if (!in_array($dataType, ["string", "double", "boolean", "integer"])) {
                $dataType = "serializable";
            }
            $dataType = ucfirst($dataType);
            $value = call_user_func([$this, "convert{$dataType}"], $value);
            $value = "{$key}={$value}";
        });

        return implode(",", $elements);
    }

    protected function convertSerializable($value)
    {
        return "{$value}";
    }

    protected function convertString($value)
    {
        return "\"{$value}\"";
    }

    protected function convertInteger($value)
    {
        return (($this->getOptions()->getForceIntegers()) ? "{$value}i" : $value);
    }

    protected function convertDouble($value)
    {
        return $value;
    }

    protected function convertBoolean($value)
    {
        return (($value) ? "true" : "false");
    }
}
