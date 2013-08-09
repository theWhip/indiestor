<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

/*
	-- CAMEL CASE NAMING --
	'my-great-home' becomes 'MyGreatHome'
	normally used for entities (things, nouns)
*/

function actionCamelCaseName($actionName)
{
        $stringParts=explode('-',$actionName);
        $stringParts2=array();
        foreach($stringParts as $stringPart)
        {
                $stringParts2[]=ucfirst($stringPart);
        }
        $actionCameCaseName=implode('',$stringParts2);
        return $actionCameCaseName;
}

/*
	-- CAMEL CASE NAMING WITH FIRST WORD IN LOWER CASE --
	'set-my-home' becomes 'setMyHome'
	normally used for actions (verbs)
*/

function actionCamelCaseNameWithFirstLowerCase($actionName)
{
        $stringParts=explode('-',$actionName);
        $stringParts2=array();
        $i=0;
        foreach($stringParts as $stringPart)
        {
                if($i==0) $stringParts2[]=strtolower($stringPart);
                else $stringParts2[]=ucfirst($stringPart);
                $i++;
        }
        $actionCamelCaseNameWithFirstLowerCase=implode('',$stringParts2);
        return $actionCamelCaseNameWithFirstLowerCase;
}

