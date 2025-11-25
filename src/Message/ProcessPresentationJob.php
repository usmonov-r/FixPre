<?php

namespace App\Message;


class ProcessPresentationJob
{
    public function __construct(
        private string $jobId,
        private string $filepath,
    ) {
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getFilePath(): string
    {
        return $this->filepath;
    }
}
