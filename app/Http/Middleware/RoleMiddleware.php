<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  array|string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        // 🚶‍♂️ إذا ما في مستخدم (ضيف فعلي)
        if (!$user) {
            // إذا "guest" ضمن الأدوار المسموحة → اسمح له
            if (in_array('guest', $roles)) {
                return $next($request);
            }

            // غير هيك → ممنوع
            return response()->json(['message' => 'Please log in first.'], 401);
        }

        // ✅ لو المستخدم مسجل دخول، افحص دوره من قاعدة البيانات
        if (!in_array($user->role, $roles)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // ✅ لو كل شي تمام
        return $next($request);
    }
}
