<i class="fa fa-comments-o"></i>

<div class="timeline-item" id="timeline-item-{{ feed.id }}">
    <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>

    <h3 class="timeline-header">
        <span class="timeline-feed-avatar">
            <img v-bind:src="createdUserImg">
        </span>
        <span class="timeline-feed-header-text">
            {{{headerText}}}
        </span>
    </h3>

    <div class="timeline-body">
        <div class="timeline-email-body">{{{feed.message}}}</div>
    </div>
</div>