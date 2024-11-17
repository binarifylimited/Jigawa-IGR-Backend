<?php
// Helper function to convert an associative array to XML format
function jsonToXml($json, $rootElement = "root") {
    // Step 1: Convert JSON to PHP array
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException("Invalid JSON data provided");
    }

    // Step 2: Convert PHP array to XML
    $xml = new SimpleXMLElement("<{$rootElement}/>");
    arrayToXml($data, $xml);
    return $xml->asXML();
}

function arrayToXml($data, &$xml) {
    foreach ($data as $key => $value) {
        // Use 'item' as the XML node name for numeric keys
        if (is_numeric($key)) {
            $key = "item";
        }

        // Recursively handle nested arrays
        if (is_array($value)) {
            $subnode = $xml->addChild($key);
            arrayToXml($value, $subnode);
        } else {
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
}