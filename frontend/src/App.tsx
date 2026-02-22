import { useRef, useState, useEffect } from 'react'
import { AnimatePresence, motion } from 'framer-motion'

const AI_STREAM_ENDPOINT = 'http://127.0.0.1:8000/api/ai/stream'

function App() {
  const [text, setText] = useState('')
  const [isStreaming, setIsStreaming] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [hasContent, setHasContent] = useState(false)
  const abortControllerRef = useRef<AbortController | null>(null)

  useEffect(() => {
    setHasContent(text.length > 0)
  }, [text])

  const startStreaming = async () => {
    if (isStreaming) return

    setText('')
    setError(null)
    setIsStreaming(true)

    const abortController = new AbortController()
    abortControllerRef.current = abortController

    try {
      const response = await fetch(AI_STREAM_ENDPOINT, {
        method: 'GET',
        signal: abortController.signal,
      })

      if (!response.ok || !response.body) {
        throw new Error(`Streaming failed with status ${response.status}`)
      }

      const reader = response.body.getReader()
      const decoder = new TextDecoder('utf-8')

      while (true) {
        const { value, done } = await reader.read()
        if (done) break

        const chunk = decoder.decode(value, { stream: true })
        setText((prev) => prev + chunk)
      }
    } catch (err: any) {
      if (err.name !== 'AbortError') {
        setError(err.message ?? 'Unexpected error while streaming')
      }
    } finally {
      setIsStreaming(false)
      abortControllerRef.current = null
    }
  }

  const stopStreaming = () => {
    if (abortControllerRef.current) {
      abortControllerRef.current.abort()
    }
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950/20 via-slate-900 to-slate-950 text-slate-100 flex items-center justify-center px-4 py-12">
      <motion.div
        initial={{ opacity: 0, scale: 0.96, y: 20 }}
        animate={{ opacity: 1, scale: 1, y: 0 }}
        transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
        className="relative w-full max-w-4xl rounded-3xl border border-white/10 bg-white/5 shadow-2xl backdrop-blur-md px-10 py-8"
      >
        <div className="pointer-events-none absolute inset-0 rounded-3xl border border-white/5" />
        <div className="pointer-events-none absolute inset-0 rounded-3xl bg-gradient-to-br from-white/5 via-transparent to-transparent opacity-50" />

        <header className="mb-8 flex flex-col items-center justify-center gap-4 text-center">
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1, duration: 0.3 }}
            className="flex flex-col items-center"
          >
            <h1 className="text-3xl font-semibold tracking-tight bg-gradient-to-r from-slate-100 to-slate-400 bg-clip-text text-transparent">
              AI Text Streamer
            </h1>
            <p className="mt-2 text-sm text-slate-400">
              Laravel 11 + React (Vite) streaming demo
            </p>
          </motion.div>
          <motion.div
            initial={{ opacity: 0, y: -5 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.15, duration: 0.3 }}
            className="flex flex-col items-center gap-2"
          >
            <AnimatePresence>
              {isStreaming && (
                <motion.span
                  initial={{ opacity: 0, scale: 0.8 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.8 }}
                  transition={{ duration: 0.2 }}
                  className="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-300"
                >
                  Live stream
                  <span className="ml-2 h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.9)] animate-pulse" />
                </motion.span>
              )}
            </AnimatePresence>
            
          </motion.div>
        </header>

        <div className="relative z-10 space-y-6">
          <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2, duration: 0.3 }}
            className="flex flex-wrap items-center justify-center gap-3"
          >
            <motion.button
              type="button"
              onClick={startStreaming}
              disabled={isStreaming}
              whileHover={!isStreaming ? { scale: 1.02 } : {}}
              whileTap={!isStreaming ? { scale: 0.98 } : {}}
              transition={{ duration: 0.2 }}
              className={`inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950
                ${
                  isStreaming
                    ? 'cursor-not-allowed bg-slate-700/70 text-slate-400'
                    : 'bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 text-slate-50 shadow-lg shadow-indigo-500/30 hover:from-indigo-400 hover:via-violet-400 hover:to-fuchsia-400 hover:shadow-indigo-500/40'
                }`}
            >
              {isStreaming ? 'Streaming...' : 'Start Streaming'}
            </motion.button>

            <motion.button
              type="button"
              onClick={stopStreaming}
              disabled={!isStreaming}
              whileHover={isStreaming ? { scale: 1.02 } : {}}
              whileTap={isStreaming ? { scale: 0.98 } : {}}
              transition={{ duration: 0.2 }}
              className={`inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-medium border transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950
                ${
                  isStreaming
                    ? 'border-slate-500/60 bg-slate-900/60 text-slate-100 hover:bg-slate-800/80'
                    : 'cursor-not-allowed border-slate-800 text-slate-500'
                }`}
            >
              Stop
            </motion.button>
          </motion.div>

          <AnimatePresence mode="wait">
            {error && (
              <motion.div
                key="error"
                initial={{ opacity: 0, y: -8, scale: 0.95 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                exit={{ opacity: 0, y: -8, scale: 0.95 }}
                transition={{ duration: 0.2 }}
                className="rounded-lg border border-red-900/60 bg-red-950/60 px-4 py-3 text-sm text-red-300 text-center"
              >
                {error}
              </motion.div>
            )}
          </AnimatePresence>

          <motion.div
            layout
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.3 }}
            className="relative rounded-2xl border border-white/10 bg-slate-950/70 px-6 py-6 min-h-[200px] shadow-inner shadow-slate-900/60 flex items-center justify-center"
          >
            <AnimatePresence mode="wait">
              {hasContent ? (
                <motion.div
                  key="content"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  transition={{ duration: 0.2 }}
                  className="text-sm font-mono leading-relaxed tracking-tight whitespace-pre-wrap text-left w-full"
                >
                  {text}
                  {isStreaming && (
                    <motion.span
                      initial={{ opacity: 0 }}
                      animate={{ opacity: [0, 1, 0] }}
                      transition={{
                        duration: 1,
                        repeat: Infinity,
                        ease: 'easeInOut',
                      }}
                      className="ai-cursor ml-[2px] inline-block w-[0.5ch] align-baseline text-slate-300"
                    >
                      |
                    </motion.span>
                  )}
                </motion.div>
              ) : (
                <motion.p
                  key="placeholder"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  transition={{ duration: 0.2 }}
                  className="text-sm leading-relaxed text-slate-500 text-center"
                >
                  Press{' '}
                  <span className="font-semibold text-slate-200">Start Streaming</span>{' '}
                  to watch your AI response stream in real time. You can stop the
                  stream at any point and keep the text that has already been
                  generated.
                </motion.p>
              )}
            </AnimatePresence>
          </motion.div>
        </div>
      </motion.div>
    </div>
  )
}

export default App
