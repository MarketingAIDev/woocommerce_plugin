<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CampaignsListsSegment
 * @package Acelle\Model
 * 
 * @property integer id
 * @property integer campaign_id
 * @property Campaign campaign
 * @property integer mail_list_id
 * @property MailList mail_list
 * @property integer segment_id
 * @property Segment segment
 * @property integer segment2_id
 * @property Segment2 segment2
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 */
class CampaignsListsSegment extends Model
{
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function mailList()
    {
        return $this->belongsTo(MailList::class);
    }

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function segment2()
    {
        return $this->belongsTo(Segment2::class);
    }

    /**
     * Get segment in the same campaign and mail list.
     *
     * @return collect
     */
    public function getRelatedSegments()
    {
        $segments = Segment::leftJoin('campaigns_lists_segments', 'campaigns_lists_segments.segment_id', '=', 'segments.id')
                        ->where('campaigns_lists_segments.campaign_id', '=', $this->campaign_id)
                        ->where('campaigns_lists_segments.mail_list_id', '=', $this->mail_list_id);

        return $segments->get();
    }
}
