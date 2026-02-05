// --- ICON COLLECTION ---
    const Icons = {
        Dashboard: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>,
        Songs: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>,
        Settings: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>,
        Media: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>,
        System: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>,
        Mail: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>,
        Upload: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>,
        Trash: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>,
        Edit: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>,
        Check: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="20 6 9 17 4 12"></polyline></svg>,
        Copy: () => <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
    };

    // --- UI BASE COMPONENTS ---
    
    // Toast Notification System
    const Toast = ({ message, type, onClose }) => {
        useEffect(() => {
            const timer = setTimeout(onClose, 3000);
            return () => clearTimeout(timer);
        }, [onClose]);

        const bg = type === 'error' ? 'bg-red-500' : 'bg-accent';
        return (
            <div className={`fixed top-6 right-6 ${bg} text-white px-6 py-4 rounded-xl shadow-2xl z-50 animate-in slide-in-from-right fade-in duration-300 flex items-center gap-3`}>
                <span className="font-bold text-sm tracking-wide">{message}</span>
                <button onClick={onClose} className="opacity-50 hover:opacity-100 font-bold">?</button>
            </div>
        );
    };

    const LoadingSpinner = () => (
        <div className="flex justify-center items-center p-20">
            <div className="spinner"></div>
            <span className="ml-4 text-slate-400 text-xs font-black uppercase tracking-widest animate-pulse">Lade Daten...</span>
        </div>
    );

    const DashboardCard = ({ title, value, subtext, icon, color = "accent" }) => (
        <div className="glass p-6 rounded-[2rem] border border-white/5 relative overflow-hidden group hover:border-accent/30 transition-all duration-300 hover:-translate-y-1">
            <div className={`absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity text-${color} transform group-hover:scale-110 duration-500`}>
                <div style={{ transform: 'scale(2.5)' }}>{icon}</div>
            </div>
            <h3 className="text-[10px] font-black uppercase text-slate-500 tracking-widest mb-3">{title}</h3>
            <div className="text-4xl font-black text-white mb-2 tracking-tight">{value}</div>
            <div className="text-[10px] text-slate-400 font-medium uppercase tracking-wide">{subtext}</div>
            <div className={`h-1 w-full bg-gradient-to-r from-${color}/50 to-transparent mt-4 rounded-full opacity-30`}></div>
        </div>
    );

    const InputField = ({ label, value, onChange, placeholder, type = "text", multiline = false, helpText = null }) => (
        <div className="space-y-2 group">
            <div className="flex justify-between">
                <label className="text-[10px] font-black uppercase text-slate-500 tracking-widest ml-1 group-focus-within:text-accent transition-colors">{label}</label>
                {helpText && <span className="text-[9px] text-slate-600 italic">{helpText}</span>}
            </div>
            {multiline ? (
                <textarea 
                    className="w-full bg-slate-950/50 p-4 rounded-xl border border-white/10 text-sm focus:border-accent outline-none min-h-[120px] transition-all focus:bg-slate-950 font-medium text-slate-300 placeholder:text-slate-700" 
                    value={value || ''} 
                    onChange={onChange} 
                    placeholder={placeholder} 
                />
            ) : (
                <input 
                    type={type} 
                    className="w-full bg-slate-950/50 p-4 rounded-xl border border-white/10 text-sm focus:border-accent outline-none transition-all focus:bg-slate-950 font-medium text-slate-300 placeholder:text-slate-700 h-12" 
                    value={value || ''} 
                    onChange={onChange} 
                    placeholder={placeholder} 
                />
            )}
        </div>
    );

    const ToggleSwitch = ({ label, checked, onChange }) => (
        <div className="flex items-center justify-between bg-slate-950/30 p-4 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
            <span className="text-[10px] font-black uppercase text-slate-400 tracking-widest">{label}</span>
            <div 
                className={`w-12 h-6 rounded-full cursor-pointer transition-colors relative ${checked ? 'bg-accent' : 'bg-slate-700'}`} 
                onClick={() => onChange(!checked)}
            >
                <div className={`absolute top-1 w-4 h-4 rounded-full bg-white transition-all shadow-md ${checked ? 'left-7' : 'left-1'}`}></div>
            </div>
        </div>
    );