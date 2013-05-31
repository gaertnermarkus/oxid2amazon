
[{if $order->oxorder__amzorderid->value != ""}]
    [{oxcontent ident="oxadminorderemail_amazon"}]
[{/if}]

[{$smarty.block.parent}]