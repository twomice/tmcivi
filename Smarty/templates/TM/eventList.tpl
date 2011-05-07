{*
 * $Id$
*}

{* Displays given Events Listing as an HTML page. *}

<p>Our records indicate you are, or have been, registered for the following events:</p>
{include file="CRM/common/jsortable.tpl"}
<table id="options" class="display">
<thead>
<tr>
    <th>{ts}Event{/ts}</th>
    <th>{ts}When{/ts}</th>
    <th>{ts}Location{/ts}</th>
    <th>{ts}Email{/ts}</th>
    <th id="nosort">{ts}Status{/ts}</th>
    <th id="nosort">{ts}Registration{/ts}</th>
</tr>
</thead>

{foreach from=$MOD_events key=uid item=event}
{assign var=eventId value=$event.event_id}
<tr class="{cycle values="odd-row,even-row"} {$row.class}">
    <td><a href="{crmURL p='civicrm/event/info' q="reset=1&id=`$event.event_id`"}" title="{ts}read more{/ts}"><strong>{$event.title}</strong></a></td>
    <td class="nowrap">
        {if $event.start_date}{$event.start_date|crmDate}{if $event.end_date}<br /><em>{ts}through{/ts}</em><br />{strip}
            {* Only show end time if end date = start date *}
            {if $event.end_date|date_format:"%Y%m%d" == $event.start_date|date_format:"%Y%m%d"}
                {$event.end_date|date_format:"%I:%M %p"}
            {else}
                {$event.end_date|crmDate}
            {/if}{/strip}{/if}
        {else}{ts}(not available){/ts}{/if}
    </td>
    <td>{if $event.is_show_location EQ 1 AND $event.location}{$event.location}{else}{ts}(not available){/ts}{/if}</td>
    <td>{if $event.contact_email}<a href="mailto:{$event.contact_email}">{$event.contact_email}</a>{else}&nbsp;{/if}</td>

    {if $MOD_participations.$eventId.participant_status_id == 'Pending from incomplete transaction'}
        {capture name=submitPaymentLink}<p>{l ref=submitEventPaymentOnline caption="Pay online" button=true args="participationid=`$MOD_participations.$eventId.participant_id`"}</p>{/capture}
        {assign var=statusTdClass value="TM_statusAlert"}
    {/if}

    <td class="{$statusTdClass}">
        {$MOD_participations.$eventId.participant_status_id}
        {$smarty.capture.submitPaymentLink}
    </td>
    <td>{l ref=viewMyEventRegistration button=true caption=View args="participationid=`$MOD_participations.$eventId.participant_id`"} {l ref=editMyEventRegistration button=true caption=Edit args="participationid=`$MOD_participations.$eventId.participant_id`"} </td>
</tr>
{/foreach}
</table>
