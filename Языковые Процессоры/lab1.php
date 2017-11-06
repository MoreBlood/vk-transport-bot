<?php

new Hash;

class Hash
{
    private $FreePtr = 0, $HashTable, $Identifiers, $IdentifiersTable;

    function __construct()
    {
        $this->Identifiers = array("hello", "hi", "home", "brother", "car", "ball", "pet", "dog", "cat", "flower", "table", "mouse", "phone", "cable", "keyboard", "earphones", "hard_disk", "dicker");
        foreach ($this->Identifiers as $key => $identifier) {
            $hash = $this->GetHash($identifier);
            $this->IdentifiersTable[$this->FreePtr] = new Identifier($identifier, NULL);

            if (!isset($this->HashTable[$hash]))
                $this->HashTable[$hash] = $this->FreePtr;
            else {
                if (NULL == ($this->IdentifiersTable[$this->HashTable[$hash]]->GetLink()))
                    $this->IdentifiersTable[$this->HashTable[$hash]]->ChangeLink($key);
                else {
                    $current = $this->IdentifiersTable[$this->HashTable[$hash]]->GetLink();
                    while (NULL !== $this->IdentifiersTable[$current]->GetLink()) {
                        $current = $this->IdentifiersTable[$current]->GetLink();
                    }
                    $this->IdentifiersTable[$current]->ChangeLink($key);
                }
            }
            $this->FreePtr++;
        }
        $this->GetIdentifier("hi");
        echo "";
    }

    private function GetHash($Identifier)
    {
        return ord($Identifier);
    }

    private function GetIdentifier($ID)
    {
        if ($this->IdentifiersTable[$this->HashTable[$this->GetHash($ID)]]->GetID() == $ID) {
            echo "Found from hash!";
            return 0;
        } else {
            $current = $this->HashTable[$this->GetHash($ID)];
            $counter = 0;
            while (NULL !== ($pos = $this->IdentifiersTable[$current]->GetLink())) {
                $counter++;
                if ($this->IdentifiersTable[$pos]->GetID() == $ID) {
                    echo "Found with " . $counter . " collisions";
                    return 0;
                }
                $current = $pos;
            }
        }
        echo "Not Found!";
        return 0;
    }
}

class Identifier
{
    private $ID;
    private $Link;

    function __construct($id, $link)
    {
        $this->ID = $id;
        $this->Link = $link;
    }

    public function ChangeLink($link)
    {
        $this->Link = $link;
    }

    public function GetLink()
    {
        return $this->Link;
    }

    public function GetID()
    {
        return $this->ID;
    }
}
