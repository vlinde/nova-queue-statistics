Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'nova-queue-statistics',
      path: '/nova-queue-statistics',
      component: require('./components/Tool'),
    },
  ])
})
