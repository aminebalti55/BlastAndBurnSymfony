<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * sessionReport
 *
 * @ORM\Table(name="session_report")
 * @ORM\Entity
 */
class sessionReport
{
    /**
     * @var Report
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity=Report::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="report_id", referencedColumnName="report_id")
     * })
     */
    private $report;

    /**
     * @var session
     *
     * @ORM\ManyToOne(targetEntity=session::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="session_id", referencedColumnName="session_id")
     * })
     */
    private $session;

    /**
     * @return Report
     */
    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport(Report $report): void
    {
        $this->report = $report;
    }

    /**
     * @return session
     */
    public function getsession(): session
    {
        return $this->session;
    }

    /**
     * @param session $session
     */
    public function setsession(session $session): void
    {
        $this->session = $session;
    }


}
