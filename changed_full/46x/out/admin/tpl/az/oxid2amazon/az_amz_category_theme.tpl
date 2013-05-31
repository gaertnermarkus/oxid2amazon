[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{ if $readonly }]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="az_amz_category_theme">    
</form>

<form name="myedit" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post" style="padding: 0px;margin: 0px;height:0px;">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="cl" value="az_amz_category_theme">
    <input type="hidden" name="fnc" value="saveAmazonCats">
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="voxid" value="[{ $oxid }]">
    <input type="hidden" name="editval[oxcategories__oxid]" value="[{ $oxid }]">  

    [{assign var='aAmazonCatsFromCSV' value=$oView->getAmazonCategoriesFromCSV()}]
    [{assign var='aAmazonCats' value=$oView->getAmazonCategories4category($oxid)}]

    <table border="0px"> 
        <tr>
            <td>Kategorie-1:</td>
            <td>
                <input type="hidden" maxlength="15" value="[{$aAmazonCats[0].OXOSORT}]" name="amazonCats[1][SORT]">
                [{*<input type="text" maxlength="15" value="[{$aAmazonCats[0].D3AMAZONCATID}]" name="amazonCats[1][CATID]">*}]

                <select name="amazonCats[0][CATID]">
                    <option value=""> - </option>
                    [{foreach from=$aAmazonCatsFromCSV item=aAmazonCat}]                    
                        <option value="[{$aAmazonCat.BrowseNode}]" [{if $aAmazonCat.BrowseNode == $aAmazonCats[0].D3AMAZONCATID}]selected[{/if}]>[{$aAmazonCat.Cat}] ([{$aAmazonCat.BrowseNode}])</option>                        
                    [{/foreach}]
                </select>

            </td>
        </tr>
        <tr>
            <td>Kategorie-2:</td>
            <td>
                <input type="hidden" maxlength="15" value="[{$aAmazonCats[1].OXOSORT}]" name="amazonCats[2][SORT]">
                [{*<input type="text" maxlength="15" value="[{$aAmazonCats[1].D3AMAZONCATID}]" name="amazonCats[2][CATID]">*}]
                <select name="amazonCats[1][CATID]">
                    <option value=""> - </option>
                    [{foreach from=$aAmazonCatsFromCSV item=aAmazonCat}]                    
                        <option value="[{$aAmazonCat.BrowseNode}]" [{if $aAmazonCat.BrowseNode == $aAmazonCats[1].D3AMAZONCATID}]selected[{/if}]>[{$aAmazonCat.Cat}] ([{$aAmazonCat.BrowseNode}])</option>                        
                    [{/foreach}]
                </select>
            </td>
        </tr>
        <tr>
            <td>Kategorie-3:</td>
            <td>
                <input type="hidden" maxlength="15" value="[{$aAmazonCats[2].OXOSORT}]" name="amazonCats[3][SORT]">
                [{*<input type="text" maxlength="15" value="[{$aAmazonCats[2].D3AMAZONCATID}]" name="amazonCats[3][CATID]">*}]
                <select name="amazonCats[2][CATID]" disabled="true">
                    <option value=""> - </option>
                    [{foreach from=$aAmazonCatsFromCSV item=aAmazonCat}]                    
                        <option value="[{$aAmazonCat.BrowseNode}]" [{if $aAmazonCat.BrowseNode == $aAmazonCats[2].D3AMAZONCATID}]selected[{/if}]>[{$aAmazonCat.Cat}] ([{$aAmazonCat.BrowseNode}])</option>                        
                    [{/foreach}]
                </select>
            </td>
        </tr>
        <tr>
            <td>Kategorie-4:</td>
            <td>
                <input type="hidden" maxlength="15" value="[{$aAmazonCats[3].OXOSORT}]" name="amazonCats[4][SORT]">
                [{*<input type="text" maxlength="15" value="[{$aAmazonCats[3].D3AMAZONCATID}]" name="amazonCats[4][CATID]">*}]
                <select name="amazonCats[3][CATID]" disabled="true">
                    <option value=""> - </option>
                    [{foreach from=$aAmazonCatsFromCSV item=aAmazonCat}]                    
                        <option value="[{$aAmazonCat.BrowseNode}]" [{if $aAmazonCat.BrowseNode == $aAmazonCats[3].D3AMAZONCATID}]selected[{/if}]>[{$aAmazonCat.Cat}] ([{$aAmazonCat.BrowseNode}])</option>                        
                    [{/foreach}]
                </select>
            </td>
        </tr>
        <tr>
            <td>Kategorie-5:</td>
            <td>
                <input type="hidden" maxlength="15" value="[{$aAmazonCats[4].OXOSORT}]" name="amazonCats[5][SORT]">
                [{*<input type="text" maxlength="15" value="[{$aAmazonCats[4].D3AMAZONCATID}]" name="amazonCats[5][CATID]">*}]
                <select name="amazonCats[4][CATID]" disabled="true">
                    <option value=""> - </option>
                    [{foreach from=$aAmazonCatsFromCSV item=aAmazonCat}]                    
                        <option value="[{$aAmazonCat.BrowseNode}]" [{if $aAmazonCat.BrowseNode == $aAmazonCats[4].D3AMAZONCATID}]selected[{/if}]>[{$aAmazonCat.Cat}] ([{$aAmazonCat.BrowseNode}])</option>                        
                    [{/foreach}]
                </select>
            </td>
        </tr>

        <tr>
            <td>
                <input type="submit" class="edittext" name="save" value="[{ oxmultilang ident="CATEGORY_TEXT_SAVE" }]">
            </td>
        </tr>
</form>
</table>


<hr>
<form name="myedit" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post" style="padding: 0px;margin: 0px;height:0px;">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="cl" value="az_amz_category_theme">
    <input type="hidden" name="fnc" value="save">
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="voxid" value="[{ $oxid }]">
    <input type="hidden" name="editval[oxcategories__oxid]" value="[{ $oxid }]">    
    <table border="0px">
        <tr>
            <td>[{ oxmultilang ident="AZ_AMZ_CATEGORY_THEME" }]:</td>
            <td>
                <select name="aAmazon[theme]" onChange="Javascript:document.myedit.submit()" style="width: 250px;">
                    <option value=""> - </option>
                    [{foreach from=$aAmazonThemes item=sTheme}]
                        <option value="[{$sTheme}]" [{if $sTheme == $aCatThemeData.theme}]selected[{/if}]>[{$sTheme}]</option>
                    [{/foreach}]
                </select>
            </td>
        </tr>
        <tr>
            <td>[{ oxmultilang ident="AZ_AMZ_CATEGORY_CATEGORY" }]:</td>
            <td>
                [{if $aAmazonThemeCategories}]
                    <select name="aAmazon[category]" style="width: 250px;">
                        <option value=""> - </option>
                        [{foreach from=$aAmazonThemeCategories item=sThemeCategory}]
                            <option value="[{$sThemeCategory}]" [{if $sThemeCategory == $aCatThemeData.category}]selected[{/if}]>[{$sThemeCategory}]</option>
                        [{/foreach}]
                    </select>
                [{else}]
                    N/A
                [{/if}]
            </td>
        </tr>    
        <tr>

            <td>[{ oxmultilang ident="AZ_AMZ_CATEGORY_SUBCATEGORY" }]:</td>
            <td>
                [{if $aAmazonThemeSubCategories}]
                    <select name="aAmazon[subcategory]" style="width: 250px;">
                        <option value=""> - </option>
                        [{foreach from=$aAmazonThemeSubCategories item=sThemeSubCategory}]
                            <option value="[{$sThemeSubCategory}]" [{if $sThemeSubCategory == $aCatThemeData.subcategory}]selected[{/if}]>[{$sThemeSubCategory}]</option>
                        [{/foreach}]
                    </select>
                [{else}]
                    N/A
                [{/if}]
            </td>    
        </tr>

        <tr>

            <td>[{ oxmultilang ident="AZ_AMZ_CATEGORY_VARIATION_THEME" }]:</td>
            <td>
                [{if $aAmazonVariationThemes}]
                    <select name="aAmazon[variation]" style="width: 250px;">
                        <option value=""> - </option>
                        [{foreach from=$aAmazonVariationThemes item=sThemeVariation}]
                            <option value="[{$sThemeVariation}]" [{if $sThemeVariation == $aCatThemeData.variation}]selected[{/if}]>[{$sThemeVariation}]</option>
                        [{/foreach}]
                    </select>
                [{else}]
                    N/A
                [{/if}]
            </td>    
        </tr>
        <tr>
            <td>
                <input type="submit" class="edittext" name="save" value="[{ oxmultilang ident="CATEGORY_TEXT_SAVE" }]" onClick="Javascript:document.myedit.fnc.value='save'">
            </td>
        </tr>
</form>
</table>


[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
