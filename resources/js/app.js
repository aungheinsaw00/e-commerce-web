import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.store('notifications', {
        items: [],
        unreadCount: 0,
        nextPageUrl: null,
        loading: false,

        init() {
            this.fetchNotifications();
            this.listenForNewNotifications();
        },

        async fetchNotifications(url = '/notifications') {
            if (this.loading || (url !== '/notifications' && !this.nextPageUrl)) return;
            this.loading = true;
            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) {
                    if (response.status === 401) return; // not logged in
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                
                if (url === '/notifications') {
                    this.items = data.notifications.data;
                } else {
                    this.items = [...this.items, ...data.notifications.data];
                }
                
                this.unreadCount = data.unreadCount;
                this.nextPageUrl = data.notifications.next_page_url;
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                this.loading = false;
            }
        },

        _isListening: false,

        listenForNewNotifications() {
            if (this._isListening) return;
            
            const userIdMeta = document.querySelector('meta[name="user-id"]');
            if (userIdMeta && window.Echo) {
                this._isListening = true;
                const userId = userIdMeta.content;
                window.Echo.private(`private-user.${userId}`)
                    .listen('NotificationCreated', (e) => {
                        // Create the new notification object
                        const newNotification = {
                            id: e.id,
                            type: e.type,
                            title: e.title,
                            message: e.message,
                            read_at: null,
                            created_at: e.created_at,
                            data: e.data
                        };
                        // Reassign array to trigger Alpine reactivity
                        this.items = [newNotification, ...this.items];
                        this.unreadCount++;
                    });
            }
        }
    });
});
