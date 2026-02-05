<?php require_once 'header.php'; ?>

    /**
     * admin.php - Zentrale Administration
     * Diese Datei setzt den Babel-Kontext aus der header.php fort.
     */

    <?php 
    // Inklusion der UI-Komponenten und Seiten-Ansichten
    require_once 'admin_components.php'; 
    require_once 'admin_views.php'; 
    ?>

    // --- ADMIN HAUPTKOMPONENTE ---
    const App = () => {
        // --- STATE MANAGEMENT ---
        const [view, setView] = useState('dashboard');
        const [songs, setSongs] = useState([]);
        const [media, setMedia] = useState([]);
        const [stats, setStats] = useState(null);
        const [settings, setSettings] = useState({});
        
        const [loading, setLoading] = useState(false);
        const [auth, setAuth] = useState(false);
        const [pass, setPass] = useState('');
        
        // UI & Modals
        const [notification, setNotification] = useState(null);
        const [editingSong, setEditingSong] = useState(null);
        const [isEditing, setIsEditing] = useState(false);
        
        // System & Marketing Tools
        const [sqlQuery, setSqlQuery] = useState('');
        const [systemFile, setSystemFile] = useState(null);
        const [targetFileName, setTargetFileName] = useState('api.php');
        const [newsletterSubject, setNewsletterSubject] = useState('');
        const [newsletterBody, setNewsletterBody] = useState('');

        const ADMIN_CODE = SERVER_ADMIN_CODE;

        // --- AUTHENTIFIZIERUNG ---
        const handleLogin = (e) => {
            if (e) e.preventDefault();
            if (pass === ADMIN_CODE) {
                setAuth(true);
                localStorage.setItem('admin_auth', 'true');
                showNotify('System entsperrt', 'success');
            } else {
                showNotify('Ungültiger Zugriffscode', 'error');
            }
        };

        useEffect(() => {
            if (localStorage.getItem('admin_auth') === 'true') {
                setAuth(true);
            }
        }, []);

        // --- API KOMMUNIKATION ---
        const fetchApi = async (action, options = {}) => {
            const headers = {
                'X-Admin-Code': ADMIN_CODE,
                ...options.headers
            };
            
            if (!(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }

            try {
                const res = await fetch(`api.php?action=${action}`, { ...options, headers });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'API Fehler');
                return data;
            } catch (err) {
                showNotify(err.message, 'error');
                throw err;
            }
        };

        // --- DATEN INITIALISIERUNG ---
        const loadData = async () => {
            if (!auth) return;
            setLoading(true);
            try {
                const [sData, mData, stData, settsData] = await Promise.all([
                    fetchApi('getSongs'),
                    fetchApi('getMedia'),
                    fetchApi('getStats'),
                    fetchApi('getSettings')
                ]);
                setSongs(sData);
                setMedia(mData);
                setStats(stData);
                setSettings(settsData);
            } catch (e) { 
                console.error("Ladefehler:", e); 
            }
            setLoading(false);
        };

        useEffect(() => { loadData(); }, [auth]);

        // --- UI HELPER ---
        const showNotify = (msg, type = 'info') => {
            setNotification({ msg, type });
            setTimeout(() => setNotification(null), 3500);
        };

        // --- HANDLER: SONG MANAGEMENT ---
        const handleSaveSong = async (formData) => {
            setLoading(true);
            try {
                const res = await fetchApi('saveSong', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
                if (res.success) {
                    showNotify('Release erfolgreich aktualisiert', 'success');
                    setIsEditing(false);
                    loadData();
                }
            } catch (e) { showNotify('Speichern fehlgeschlagen', 'error'); }
            setLoading(false);
        };

        const handleDeleteSong = async (id) => {
            if (!window.confirm("Release endgültig aus der Datenbank entfernen?")) return;
            try {
                const res = await fetchApi('deleteSong', {
                    method: 'POST',
                    body: JSON.stringify({ id })
                });
                if (res.success) {
                    showNotify('Song wurde gelöscht', 'success');
                    loadData();
                }
            } catch (e) { showNotify('Löschfehler', 'error'); }
        };

        // --- HANDLER: MEDIA & DATEIEN ---
        const handleMediaDelete = async (id) => {
            if (!window.confirm("Datei physisch vom Server löschen?")) return;
            try {
                const res = await fetchApi('deleteMedia', {
                    method: 'POST',
                    body: JSON.stringify({ id })
                });
                if (res.success) {
                    showNotify('Datei entfernt', 'success');
                    loadData();
                }
            } catch (e) { showNotify('Fehler beim Löschen der Mediendatei', 'error'); }
        };

        const handleMediaUpload = async (e) => {
            const files = e.target.files;
            if (!files.length) return;
            setLoading(true);
            try {
                for (let file of files) {
                    const fd = new FormData();
                    fd.append('file', file);
                    await fetchApi('uploadMedia', { method: 'POST', body: fd });
                }
                showNotify('Alle Dateien erfolgreich hochgeladen', 'success');
                loadData();
            } catch (e) { showNotify('Upload-Fehler aufgetreten', 'error'); }
            setLoading(false);
        };

        // --- HANDLER: SYSTEM TOOLS ---
        const handleSendNewsletter = async () => {
            if (!newsletterSubject || !newsletterBody) return showNotify('Bitte Betreff und Inhalt angeben', 'error');
            setLoading(true);
            try {
                const res = await fetchApi('sendNewsletter', {
                    method: 'POST',
                    body: JSON.stringify({ subject: newsletterSubject, body: newsletterBody })
                });
                if (res.success) {
                    showNotify(`${res.sent_count} Newsletter wurden versendet`, 'success');
                    setNewsletterSubject(''); setNewsletterBody('');
                }
            } catch (e) { showNotify('Versandfehler im System', 'error'); }
            setLoading(false);
        };

        const handleSqlRun = async () => {
            if (!sqlQuery) return;
            try {
                const res = await fetchApi('runSql', {
                    method: 'POST',
                    body: JSON.stringify({ query: sqlQuery })
                });
                console.table(res.result);
                showNotify('SQL ausgeführt. Ergebnis in der Browser-Konsole.', 'success');
            } catch (e) { showNotify('Fehler in der SQL-Syntax', 'error'); }
        };

        const handleSystemFileUpdate = async () => {
            if (!systemFile) return showNotify('Keine Patch-Datei ausgewählt', 'error');
            const fd = new FormData();
            fd.append('file', systemFile);
            fd.append('target', targetFileName);
            try {
                const res = await fetchApi('updateSystemFile', { method: 'POST', body: fd });
                if (res.success) showNotify(`${targetFileName} erfolgreich gepatcht!`, 'success');
            } catch (e) { showNotify('Patch-Vorgang fehlgeschlagen', 'error'); }
        };

        // --- RENDER LOGIN ---
        if (!auth) {
            return (
                <div className="min-h-screen flex items-center justify-center bg-[#020617] p-6 selection:bg-accent selection:text-black">
                    <div className="glass p-12 rounded-[3rem] border border-white/5 w-full max-w-md animate-in fade-in zoom-in duration-700 shadow-[0_0_80px_rgba(0,0,0,0.5)]">
                        <div className="w-20 h-20 bg-accent/10 rounded-3xl flex items-center justify-center text-accent mx-auto mb-10 shadow-inner">
                            <Icons.System />
                        </div>
                        <h1 className="text-2xl font-black text-center uppercase tracking-tighter mb-2 italic">Stealth Core</h1>
                        <p className="text-[10px] text-center text-slate-500 font-bold uppercase tracking-[0.4em] mb-12">Authorization Required</p>
                        <form onSubmit={handleLogin} className="space-y-6">
                            <input 
                                type="password" 
                                className="w-full bg-slate-900/50 p-5 rounded-2xl border border-white/10 text-center text-sm focus:border-accent outline-none transition-all placeholder:text-slate-800 tracking-[0.5em]"
                                placeholder="••••"
                                value={pass}
                                onChange={e => setPass(e.target.value)}
                                autoFocus
                            />
                            <button className="w-full bg-accent text-white py-5 rounded-2xl font-black uppercase text-[10px] tracking-[0.2em] shadow-xl shadow-accent/20 hover:scale-105 active:scale-95 transition-all">Unlock Engine</button>
                        </form>
                    </div>
                </div>
            );
        }

        // --- RENDER DASHBOARD ---
        return (
            <div className="min-h-screen bg-[#020617] text-slate-200 selection:bg-accent selection:text-slate-950 font-sans">
                
                {/* NOTIFICATION */}
                {notification && (
                    <div className={`fixed top-12 left-1/2 -translate-x-1/2 z-[200] px-10 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-2xl animate-in slide-in-from-top-12 duration-500 flex items-center gap-4 ${notification.type === 'success' ? 'bg-accent text-slate-950' : 'bg-red-500 text-white'}`}>
                        <div className="w-2 h-2 rounded-full bg-current animate-pulse"></div>
                        {notification.msg}
                    </div>
                )}

                {/* MODAL: SONG EDITOR */}
                {isEditing && (
                    <SongEditForm 
                        song={editingSong} 
                        onSave={handleSaveSong} 
                        onCancel={() => setIsEditing(false)} 
                    />
                )}

                {/* SIDEBAR NAVIGATION */}
                <aside className="fixed left-0 top-0 h-full w-24 md:w-80 glass border-r border-white/5 z-50 flex flex-col bg-[#020617]/40 backdrop-blur-3xl">
                    <div className="p-10 md:p-14 mb-8 text-center md:text-left">
                        <h2 className="text-2xl font-black text-white italic tracking-tighter leading-none hidden md:block">STEALTH <span className="text-accent">CORE</span></h2>
                        <div className="w-12 h-12 bg-accent rounded-2xl flex md:hidden items-center justify-center text-slate-950 mx-auto font-black shadow-lg shadow-accent/20">S</div>
                        <p className="text-[9px] text-slate-600 font-bold uppercase tracking-[0.5em] mt-4 hidden md:block">Admin Interface v3.3</p>
                    </div>

                    <nav className="flex-1 px-6 md:px-10 space-y-3">
                        {[
                            { id: 'dashboard', label: 'Dashboard', icon: <Icons.Dashboard /> },
                            { id: 'songs', label: 'Releases', icon: <Icons.Songs /> },
                            { id: 'media', label: 'Media Library', icon: <Icons.System /> },
                            { id: 'settings', label: 'System Config', icon: <Icons.Settings /> },
                            { id: 'system', label: 'Dev Tools', icon: <Icons.System /> }
                        ].map(item => (
                            <button 
                                key={item.id}
                                onClick={() => setView(item.id)}
                                className={`w-full flex items-center justify-center md:justify-start gap-6 p-5 rounded-2xl transition-all group relative ${view === item.id ? 'bg-accent text-slate-950 shadow-2xl shadow-accent/10' : 'text-slate-500 hover:bg-white/5 hover:text-white'}`}
                            >
                                <div className={`${view === item.id ? 'text-slate-950' : 'text-slate-500 group-hover:text-accent'} transition-colors`}>{item.icon}</div>
                                <span className="text-[10px] font-black uppercase tracking-[0.2em] hidden md:block">{item.label}</span>
                                {view === item.id && <div className="absolute right-4 w-1.5 h-1.5 rounded-full bg-slate-950 hidden md:block"></div>}
                            </button>
                        ))}
                    </nav>

                    <div className="p-10 border-t border-white/5">
                        <button 
                            onClick={() => { localStorage.removeItem('admin_auth'); window.location.reload(); }}
                            className="w-full p-5 rounded-2xl text-slate-700 hover:text-red-500 hover:bg-red-500/5 transition-all text-[10px] font-black uppercase tracking-widest flex items-center justify-center md:justify-start gap-5 group"
                        >
                            <div className="group-hover:rotate-12 transition-transform"><Icons.System /></div>
                            <span className="hidden md:block">Logout</span>
                        </button>
                    </div>
                </aside>

                {/* CONTENT AREA */}
                <main className="pl-24 md:pl-80 min-h-screen relative">
                    <header className="p-10 md:p-14 flex justify-between items-center bg-gradient-to-b from-[#020617] to-transparent">
                        <div>
                            <h2 className="text-4xl font-black uppercase tracking-tighter leading-none">{view}</h2>
                            <p className="text-[10px] text-slate-500 font-bold uppercase tracking-[0.5em] mt-4">Stealth Music Operations</p>
                        </div>
                        <div className="flex items-center gap-6">
                            <div className="hidden lg:flex flex-col items-end">
                                <span className="text-[11px] font-black uppercase tracking-widest text-white">System Admin</span>
                                <span className="text-[9px] font-bold text-accent uppercase tracking-[0.3em] mt-1">Level 5 Access</span>
                            </div>
                            <div className="w-16 h-16 rounded-[1.25rem] bg-slate-900/50 border border-white/10 flex items-center justify-center shadow-inner overflow-hidden p-3">
                                <img src="https://thestealth.de/uploads/logo_main.png" className="w-full opacity-40 hover:opacity-100 transition-opacity" onError={(e) => e.target.src='https://via.placeholder.com/50'} />
                            </div>
                        </div>
                    </header>

                    <div className="p-10 md:p-14 pt-0">
                        {view === 'dashboard' && <AdminDashboardView stats={stats} loading={loading} />}
                        {view === 'songs' && (
                            <AdminSongsView 
                                songs={songs} 
                                onEdit={(s) => { setEditingSong(s); setIsEditing(true); }}
                                onDelete={handleDeleteSong}
                                onAdd={() => { setEditingSong(null); setIsEditing(true); }}
                            />
                        )}
                        {view === 'media' && (
                            <AdminMediaView 
                                media={media} 
                                onUpload={handleMediaUpload} 
                                onDelete={handleMediaDelete} 
                            />
                        )}
                        {view === 'settings' && (
                            <AdminSettingsView 
                                settings={settings} 
                                onSave={async (newSet) => {
                                    const res = await fetchApi('saveSettings', {
                                        method: 'POST',
                                        body: JSON.stringify(newSet)
                                    });
                                    if(res.success) { 
                                        showNotify('System-Konfiguration gespeichert', 'success'); 
                                        loadData(); 
                                    }
                                }}
                            />
                        )}
                        {view === 'system' && (
                            <AdminSystemView 
                                sqlQuery={sqlQuery} setSqlQuery={setSqlQuery} handleSqlRun={handleSqlRun}
                                systemFile={systemFile} setSystemFile={setSystemFile}
                                targetFileName={targetFileName} setTargetFileName={setTargetFileName}
                                handleSystemFileUpdate={handleSystemFileUpdate}
                                newsletterSubject={newsletterSubject} setNewsletterSubject={setNewsletterSubject}
                                newsletterBody={newsletterBody} setNewsletterBody={setNewsletterBody}
                                handleSendNewsletter={handleSendNewsletter}
                            />
                        )}
                    </div>

                    {/* FOOTER STATS */}
                    <footer className="p-10 border-t border-white/5 flex justify-center opacity-20">
                        <div className="flex items-center gap-8 text-[9px] font-black uppercase tracking-[0.4em]">
                             <span>Database: {stats?.total_songs || 0} Tracks</span>
                             <span className="w-1 h-1 rounded-full bg-slate-500"></span>
                             <span>Storage: {stats?.media_count || 0} Assets</span>
                        </div>
                    </footer>
                </main>

                {/* LOADER OVERLAY */}
                {loading && (
                    <div className="fixed top-0 left-0 w-full h-1 z-[300] bg-accent/20">
                        <div className="h-full bg-accent animate-loading-bar w-1/3"></div>
                    </div>
                )}

                <style>{`
                    @keyframes loading-bar {
                        0% { transform: translateX(-100%); }
                        100% { transform: translateX(300%); }
                    }
                    .animate-loading-bar { animation: loading-bar 2s infinite ease-in-out; }
                `}</style>
            </div>
        );
    };

    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);

<?php require_once 'footer.php'; ?>