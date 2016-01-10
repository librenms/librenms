<?php
namespace CodeClimate\Bundle\TestReporterBundle\Entity;

class CiInfo
{
    public function toArray()
    {
        if (isset($_SERVER["TRAVIS"])) {
            return $this->travisProperties();
        }

        if (isset($_SERVER["CIRCLECI"])) {
            return $this->circleProperties();
        }

        if (isset($_SERVER["SEMAPHORE"])) {
            return $this->semaphoreProperties();
        }

        if (isset($_SERVER["JENKINS_URL"])) {
            return $this->jenkinsProperties();
        }

        if (isset($_SERVER["TDDIUM"])) {
            return $this->tddiumProperties();
        }

        if (isset($_SERVER["CI_NAME"]) && preg_match('/codeship/i', $_SERVER["CI_NAME"])) {
            return $this->codeshipProperties();
        }

        if (isset($_SERVER["BUILDBOX"])) {
            return $this->buildboxProperties();
        }

        if (isset($_SERVER["WERCKER"])) {
            return $this->werckerProperties();
        }

        return array();
    }

    protected function travisProperties()
    {
        return array(
            "name"             => "travis-ci",
            "branch"           => $_SERVER["TRAVIS_BRANCH"],
            "build_identifier" => $_SERVER["TRAVIS_JOB_ID"],
            "pull_request"     => $_SERVER["TRAVIS_PULL_REQUEST"]
        );
    }

    protected function circleProperties()
    {
        return array(
            "name"             => "circleci",
            "build_identifier" => $_SERVER["CIRCLE_BUILD_NUM"],
            "branch"           => $_SERVER["CIRCLE_BRANCH"],
            "commit_sha"       => $_SERVER["CIRCLE_SHA1"]
        );
    }

    protected function semaphoreProperties()
    {
        return array(
            "name"             => "semaphore",
            "branch"           => $_SERVER["BRANCH_NAME"],
            "build_identifier" => $_SERVER["SEMAPHORE_BUILD_NUMBER"]
        );
    }

    protected function jenkinsProperties()
    {
        return array(
            "name"             => "jenkins",
            "build_identifier" => $_SERVER["BUILD_NUMBER"],
            "build_url"        => $_SERVER["BUILD_URL"],
            "branch"           => $_SERVER["GIT_BRANCH"],
            "commit_sha"       => $_SERVER["GIT_COMMIT"]
        );
    }

    protected function tddiumProperties()
    {
        return array(
            "name"             => "tddium",
            "build_identifier" => $_SERVER["TDDIUM_SESSION_ID"],
            "worker_id"        => $_SERVER["TDDIUM_TID"]
        );
    }

    protected function codeshipProperties()
    {
        return array(
            "name"             => "codeship",
            "build_identifier" => $_SERVER["CI_BUILD_NUMBER"],
            "build_url"        => $_SERVER["CI_BUILD_URL"],
            "branch"           => $_SERVER["CI_BRANCH"],
            "commit_sha"       => $_SERVER["CI_COMMIT_ID"]
        );
    }

    protected function buildboxProperties()
    {
        return array(
            "name"             => "buildbox",
            "build_identifier" => $_SERVER["BUILDBOX_BUILD_ID"],
            "build_url"        => $_SERVER["BUILDBOX_BUILD_URL"],
            "branch"           => $_SERVER["BUILDBOX_BRANCH"],
            "commit_sha"       => $_SERVER["BUILDBOX_COMMIT"],
            "pull_request"     => $_SERVER["BUILDBOX_PULL_REQUEST"]
        );
    }

    protected function werckerProperties()
    {
        return array(
            "name"             => "wercker",
            "build_identifier" => $_SERVER["WERCKER_BUILD_ID"],
            "build_url"        => $_SERVER["WERCKER_BUILD_URL"],
            "branch"           => $_SERVER["WERCKER_GIT_BRANCH"],
            "commit_sha"       => $_SERVER["WERCKER_GIT_COMMIT"]
        );
    }
}
