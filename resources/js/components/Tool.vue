<template>
    <div>
<!--        <h2 class="my-6 text-90 font-normal text-2xl">Queued jobs</h2>-->
<!--        <card class="bg-white flex flex-col" :class="[queuedJobs.length ? '' : ['items-center', 'justify-center']]" style="min-height: 300px">-->
<!--            <div class="py-6 px-8">-->
<!--                <ul class="list-reset">-->
<!--                    <li v-for="(record, index) in queuedJobs"-->
<!--                        class="mb-1">-->

<!--                        <div class="flex bg-gray-200">-->
<!--                            <div class="text-gray-700 bg-gray-400 px-4 py-2 m-2 w-1/4">-->
<!--                                <span>Connection: {{ record.connection }}</span>-->
<!--                            </div>-->
<!--                            <div class="text-gray-700 bg-gray-400 px-4 py-2 m-2 w-1/4">-->
<!--                                <span>Queue: {{ record.queue }}</span>-->
<!--                            </div>-->
<!--                            <div class="text-gray-700 bg-gray-400 px-4 py-2 m-2 w-1/4">-->
<!--                                <span>Count: {{ record.body }}</span>-->
<!--                            </div>-->
<!--                        </div>-->

<!--                    </li>-->
<!--                </ul>-->
<!--            </div>-->
<!--        </card>-->

        <h2 class="my-6 text-90 font-normal text-2xl">Queued Jobs Statistics</h2>
        <card class="bg-white flex flex-col" :class="[queuedStatistics.length ? '' : ['items-center', 'justify-center']]"
              style="min-height: 300px">
            <div v-if="queuedStatistics.length" class="py-6 px-8">
                <ul class="list-reset">
                    <li v-for="(record, index) in queuedStatistics" :key="index"
                        class="mb-3">

                        <div :class="[record.failed ? ['flex', 'bg-danger', 'text-white'] : ['flex', 'bg-success', 'text-white']]">
                            <div class="px-4 py-2 m-2 w-1/4">
                                <span>Connection: {{ record.connection }}</span>
                            </div>
                            <div class="px-4 py-2 m-2 w-1/4">
                                <span>Queue: {{ record.queue }}</span>
                            </div>
                            <div class="px-4 py-2 m-2 w-1/4">
                                <span>Count: {{ record.count }}</span>
                            </div>

                            <div class="ml-auto px-4 py-2">
                                <a href="#" v-if="record.failed" @click.prevent="rerunQueue(record.id)" class="btn btn-default btn-primary">
                                    Reroll!!!
                                </a>
                            </div>
                        </div>

                    </li>
                </ul>
            </div>
            <div v-else>
                <h2 class="my-6 text-90 font-normal text-2xl align-middle">There are no records</h2>
            </div>
        </card>


        <div class="flex my-6">

            <!-- <div class="relative h-9 flex-no-shrink mb-6">
                <input data-testid="search-input" dusk="search" placeholder="Search" type="search" class="appearance-none form-search w-search pl-search shadow">
            </div> -->

            <div class="relative h-9 flex-no-shrink">
                <h2 class="text-90 font-normal text-2xl">Failed jobs</h2>
            </div>

            <div class="w-full flex items-center">
                <div class="flex w-full justify-end items-center mx-3"></div>
                <div class="flex-no-shrink ml-auto mr-6">
                    <span class="mr-6">Connection:</span>
                    <select class="flex-1 form-control form-select" @change="getFailedJobs()" v-model="selectedConnection" v-if="connections.length">
                        <option selected disabled :value="0">Select a connection</option>
                        <option v-for="(connection, index) in connections" :value="connection" :key="index">
                            {{connection}}
                        </option>
                    </select>
                </div>

                <div class="flex-no-shrink ml-auto">
                    <span class="mr-6">Queue:</span>
                    <select class="flex-1 form-control form-select" @change="getFailedJobs()" v-model="selectedQueue" v-if="queuesOptions.length">
                        <option selected disabled :value="0">Select a queue for this connection</option>
                        <option v-for="(queue, index) in queuesOptions" :value="queue.queue" :key="index">
                            {{queue.queue}}
                        </option>
                    </select>
                </div>
            </div>

        </div>

        <card class="bg-white flex flex-col" :class="[Object.keys(failedJobs).length ? '' : ['items-center', 'justify-center']]"
              style="min-height: 300px">
            <div v-if="Object.keys(failedJobs).length" class="py-6 px-8">
                <ul class="list-reset">

                    <li v-for="(connections, index) in failedJobs" :key="index"
                        class="mb-1">
                        <h2 class="mb-3">Connection: {{ index }}</h2>
                        <ul class="list-reset">
                            <li v-for="(queues, index) in connections" :key="index"
                                class="mb-1">

                                <h3 class="mb-2">Queue: {{ index }}</h3>
                                <ul class="list-reset">
                                    <li v-for="(job, key, index) in queues" :key="index"
                                        class="mb-1">
                                        <div class="flex bg-gray-200">
                                            <div class="text-gray-700 bg-gray-400 px-4 py-2 m-2">
                                                <a href="#" @click.prevent="getFailedJobDetails(job.id)">{{ job.id }}</a>
                                            </div>
                                            <div class="text-gray-700 bg-gray-400 px-4 py-2 m-2">
                                                <span>Failed at: {{ job.failed_at }}</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>

                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div v-else>
                <h2 class="my-6 text-90 font-normal text-2xl align-middle">There are no records</h2>
            </div>
        </card>

            <modal :name="jobModal" v-if="openModal" @modal-close="openModal = false">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden px-4 py-4" style="width: 75vw;">
                    <heading :level="2" class="mb-6">Job #{{ job.id }}</heading>
                    <div>
                        <p>{{ job.exception }}</p>
                    </div>
                </div>
            </modal>

    </div>
</template>

<script>
    export default {
        data() {
            return {
                openModal: false,
                status: '',
                message: '',
                queuedStatistics: [],
                queuedJobs: [],
                failedJobs: [],
                selectedConnection: 0,
                connections: [],
                queues: {},
                queuesOptions: [],
                selectedQueue: 0,
                job: {}
            }
        },
        mounted() {
            this.getQueuedJobs();
        },
        methods: {
            getQueuedJobs() {
                Nova.request().post('/nova-vendor/nova-queue-statistics/get-queued-jobs', {})
                .then(response => {
                    this.queuedStatistics = response.data.statistics;
                    // this.queuedJobs = response.data.queuedJobs;
                    this.failedJobs = response.data.failedJobs;

                    this.connections = response.data.connections;

                    this.queues = response.data.queues;

                    if(this.connections.length && Object.keys(this.queues).length) {
                        this.queuesOptions = this.queues[this.connections[0]];
                    }

                });
            },
            getFailedJobs() {

                let params = {};

                if(this.selectedConnection) {
                    params.connection = this.selectedConnection;
                }

                if(this.selectedQueue) {
                    params.queue = this.selectedQueue;
                }

                Nova.request().post('/nova-vendor/nova-queue-statistics/get-failed-jobs', {
                    params
                })
                .then(response => {
                    this.failedJobs = response.data.failedJobs;
                });
            },
            getFailedJobDetails(job) {
                Nova.request().post('/nova-vendor/nova-queue-statistics/get-single-failed-job', {
                    job: job
                })
                .then(response => {
                    this.job = response.data.job;
                    this.openModal = true;
                });
            },
            rerunQueue(id) {
                 this.$toasted.show('rerun - ' + id, {type: 'success'});
                // if(response.data.success === true) {
                //     this.$toasted.show(response.data.message, {type: 'success'});
                //     window.location.reload();
                // } else {
                //     this.$toasted.show(response.data.message, {type: 'error'});
                // }
            }
        }
    }
</script>

<style>

</style>
