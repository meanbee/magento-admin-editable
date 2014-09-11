#Automate Static Block Creation

When building out a Magento site a frequent client question will be "How do I edit that bit of text?".  The common
solution to this in Magento is to use a static block.  At this point, you have to two options:

* reference a static block in the layout file, and manually create it on dev, staging, prod, etc.
* add an installation script to create the block.

If you're doing it right™ then you'll us using the latter, and writing a setup script to automatically create the block
for you on deployment.  Writing setup scripts are a pain in the ass, especially if you have multiple developers working
on the same project, commiting changes, and bumping the resource versions.  This can lead to problematic merge conflicts and
inconsistent working copies amongst developers.

This extension allows you to create static blocks on the fly from within your layout file, prepopulating it with content
(if you so wish) or adding a basic "I am this block" message.

##Installation

    modman clone git@github.com:meanbee/magento-admin-editable.git
    modman deploy magento-admin-editable

##Usage

The following examples will create blocks only if they do not already exist.  If they already exist then no creation logic will be run, and the block will be output -- just like if you'd made a `cms/block` block instead.

This will populate a block called `failover_outro` with either the contents of the file `static_blocks/failover_outro.phtml` in your theme, or it will output the message `This content is retrieved from the failover_outro static block in the administration area.`

    <block type="meanbee_admineditable/content" name="content.failover_outro">
        <action method="setStaticBlockId"><identifier>failover_outro</identifier></action>
        <action method="setStaticBlockTitle"><title>Failover Outro Text</title></action>
    </block>

This  will create the block called `failover_title` with the content `<h1>Failover License</h1>`.

    <block type="meanbee_admineditable/content" name="content.failover_title">
        <action method="setStaticBlockId"><identifier>failover_title</identifier></action>
        <action method="setStaticBlockTitle"><title>Failover Title Text</title></action>
        <action method="setStaticBlockDefaultContent"><content><![CDATA[<h1>Failover License</h1>]]></content></action>
    </block>

This  will create the block called `failover_foo` with the content of the file `sales/over/view/failover/foo.phtml`.

    <block type="meanbee_admineditable/content" name="failover_foo">
        <action method="setStaticBlockId"><identifier>failover_foo</identifier></action>
        <action method="setStaticBlockTitle"><title>Failover Foo Text</title></action>
        <action method="setStaticBlockDefaultTemplate"><template>sales/over/view/failover/foo.phtml</template></action>
    </block>
