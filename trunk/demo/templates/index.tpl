{config_load file=test.conf section="my foo"}
{include file=header.tpl title=foo}

<PRE>

Title: {#title#|capitalize}


the value of $SCRIPT_NAME is {$SCRIPT_NAME}

{* A simple variable test *}
hello, my name is {$Name|upper}

My interests are:
{section name=outer loop=$FirstName}
	{if %outer.index% is odd by 2}
		{%outer.rownum%} . {$outer/FirstName} {$outer/LastName}
	{else}
		{%outer.rownum%} * {$outer/FirstName} {$outer/LastName}
	{/if}
{sectionelse}
	none
{/section}

({$FirstName|@count})

{insert name=paginate}

testing strip tags
{strip}
<table border=0>
	<tr>
		<td>
			<A HREF="{$url}">
			<font color="red">This is a  test     </font>
			</A>
		</td>
	</tr>
</table>
{/strip}

</PRE>
