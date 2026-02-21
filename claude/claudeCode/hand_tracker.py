#!/usr/bin/env python3
"""
hand_tracker.py — Real-Time Hand Tracking POC
==============================================
Uses OpenCV and MediaPipe to capture a live webcam feed, detect hands,
and overlay detailed index-finger direction data on each frame.

Controls:
  q — quit and release the camera
"""

import cv2
import mediapipe as mp
import math
import sys


# ══════════════════════════════════════════════════════════════════════════════
#  CAMERA DETECTION & SELECTION
# ══════════════════════════════════════════════════════════════════════════════

def find_available_cameras(max_index: int = 6) -> list:
    """
    Test camera indices 0 .. max_index-1 and return the list of indices
    that both open successfully and can produce at least one frame.
    """
    available = []
    print("\nScanning for available cameras …")
    for idx in range(max_index):
        cap = cv2.VideoCapture(idx)
        if cap.isOpened():
            ret, _ = cap.read()
            if ret:
                available.append(idx)
                print(f"  [OK] Camera index {idx} — ready")
            else:
                print(f"  [--] Camera index {idx} — opened but returned no frame")
            cap.release()
        else:
            print(f"  [  ] Camera index {idx} — not found")
    return available


def select_camera(available: list) -> int:
    """
    Prompt the user to pick a camera from the detected list.
    Exits the program if no cameras are available.
    """
    if not available:
        print("\n[ERROR] No cameras detected. Connect a webcam and try again.")
        sys.exit(1)

    # Skip the prompt when there is only one option
    if len(available) == 1:
        idx = available[0]
        print(f"\nSingle camera found — using index {idx}.")
        return idx

    print(f"\nAvailable camera indices: {available}")
    while True:
        try:
            choice = int(input("Select camera index: ").strip())
            if choice in available:
                return choice
            print(f"  Please choose from {available}.")
        except ValueError:
            print("  Enter a plain integer.")


# ══════════════════════════════════════════════════════════════════════════════
#  MEDIAPIPE INITIALISATION
# ══════════════════════════════════════════════════════════════════════════════

mp_hands   = mp.solutions.hands
mp_drawing = mp.solutions.drawing_utils
mp_styles  = mp.solutions.drawing_styles

# ── Landmark indices ──────────────────────────────────────────────────────────
IDX_TIP = 8   # Index Finger Tip
IDX_MCP = 5   # Index Finger MCP (first knuckle / base of index finger)

# ── Visual constants ──────────────────────────────────────────────────────────
TIP_RADIUS     = 13            # Filled circle radius (pixels)
TIP_FILL_CLR   = (0, 230, 0)  # Bright green fill
TIP_RING_CLR   = (0, 0, 0)    # Black outline ring
RAY_CLR        = (0, 40, 255) # Bold red direction arrow
RAY_LEN        = 130          # How far the ray extends beyond the tip (px)
RAY_THICK      = 3
TEXT_CLR       = (0, 255, 255) # Cyan label text
TEXT_SHADOW    = (0, 0, 0)     # Black shadow for legibility
FONT           = cv2.FONT_HERSHEY_SIMPLEX
FONT_SCALE     = 0.65
FONT_THICK     = 2


# ══════════════════════════════════════════════════════════════════════════════
#  MATH HELPERS
# ══════════════════════════════════════════════════════════════════════════════

def compute_direction(mcp_px: tuple, tip_px: tuple):
    """
    Derive the pointing direction from the MCP base knuckle to the finger tip.

    Parameters
    ----------
    mcp_px : (int, int)   Pixel coords of Index Finger MCP (Landmark 5)
    tip_px : (int, int)   Pixel coords of Index Finger Tip (Landmark 8)

    Returns
    -------
    unit_vec  : (float, float)  Unit direction vector in *image* space (y↓)
    angle_deg : float           Angle in degrees, CCW from +X axis.
                                Image Y is flipped so "pointing up" ≈ +90°.
    """
    dx = tip_px[0] - mcp_px[0]   # positive → rightward
    dy = tip_px[1] - mcp_px[1]   # positive → downward in image coords

    # Negate dy so the angle follows standard math convention (y↑ positive)
    angle_deg = math.degrees(math.atan2(-dy, dx))

    magnitude = math.hypot(dx, dy)
    if magnitude < 1e-6:
        return (0.0, 0.0), 0.0

    return (dx / magnitude, dy / magnitude), angle_deg


def ray_endpoint(tip_px: tuple, unit_vec: tuple, length: int) -> tuple:
    """
    Compute the pixel coordinates of the far end of the direction ray.

    The ray starts at the fingertip and travels `length` pixels in the
    direction of `unit_vec` (which points MCP → Tip → beyond).
    """
    ex = int(tip_px[0] + unit_vec[0] * length)
    ey = int(tip_px[1] + unit_vec[1] * length)
    return (ex, ey)


# ══════════════════════════════════════════════════════════════════════════════
#  TEXT DRAWING HELPER (shadow + colour pass)
# ══════════════════════════════════════════════════════════════════════════════

def draw_text(frame, text: str, origin: tuple,
              font=FONT, scale=FONT_SCALE, thick=FONT_THICK,
              color=TEXT_CLR, shadow=TEXT_SHADOW):
    """
    Draw text with a 1-pass black shadow for contrast on any background.
    """
    # Shadow (slightly thicker, offset by 1 px)
    cv2.putText(frame, text, origin, font, scale, shadow, thick + 2,
                cv2.LINE_AA)
    # Foreground
    cv2.putText(frame, text, origin, font, scale, color, thick,
                cv2.LINE_AA)


# ══════════════════════════════════════════════════════════════════════════════
#  MAIN TRACKING LOOP
# ══════════════════════════════════════════════════════════════════════════════

def run(camera_index: int) -> None:
    """Open the chosen camera and run the hand-tracking loop until 'q' is pressed."""

    cap = cv2.VideoCapture(camera_index)
    if not cap.isOpened():
        print(f"[ERROR] Cannot open camera {camera_index}.")
        sys.exit(1)

    # Request a reasonable resolution (camera may not honour it exactly)
    cap.set(cv2.CAP_PROP_FRAME_WIDTH,  1280)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT,  720)

    print("\n[INFO] Hand tracker running — press 'q' to quit.\n")

    # ── MediaPipe Hands context ───────────────────────────────────────────────
    with mp_hands.Hands(
        static_image_mode=False,        # optimise for video stream
        max_num_hands=2,
        model_complexity=1,             # 0=lite, 1=full
        min_detection_confidence=0.75,
        min_tracking_confidence=0.75,
    ) as hands:

        while True:
            # ── Capture frame ─────────────────────────────────────────────────
            ret, frame = cap.read()
            if not ret or frame is None:
                print("[WARN] Empty frame — retrying …")
                continue

            # Mirror so it feels like a selfie view
            frame = cv2.flip(frame, 1)
            h, w = frame.shape[:2]

            # ── MediaPipe inference (requires RGB) ────────────────────────────
            # Marking non-writeable avoids an internal copy in MediaPipe
            rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            rgb.flags.writeable = False
            results = hands.process(rgb)
            rgb.flags.writeable = True

            # ── Per-hand rendering ────────────────────────────────────────────
            if results.multi_hand_landmarks:
                for hand_lms in results.multi_hand_landmarks:

                    # 1) Draw full MediaPipe hand skeleton (bones + joints)
                    mp_drawing.draw_landmarks(
                        frame,
                        hand_lms,
                        mp_hands.HAND_CONNECTIONS,
                        mp_styles.get_default_hand_landmarks_style(),
                        mp_styles.get_default_hand_connections_style(),
                    )

                    # 2) Convert normalised landmark coords to pixel coords
                    lm_tip = hand_lms.landmark[IDX_TIP]
                    lm_mcp = hand_lms.landmark[IDX_MCP]

                    tip_px = (int(lm_tip.x * w), int(lm_tip.y * h))
                    mcp_px = (int(lm_mcp.x * w), int(lm_mcp.y * h))

                    # 3) Compute direction vector and 2-D angle
                    #    Vector runs from MCP (Landmark 5) → Tip (Landmark 8)
                    #    representing the finger's pointing direction.
                    unit_vec, angle_deg = compute_direction(mcp_px, tip_px)

                    # 4) Find the far end of the direction ray
                    ray_end = ray_endpoint(tip_px, unit_vec, RAY_LEN)

                    # 5) Draw the direction ray — arrowed line from tip outward
                    cv2.arrowedLine(
                        frame, tip_px, ray_end,
                        RAY_CLR, RAY_THICK,
                        line_type=cv2.LINE_AA,
                        tipLength=0.28,
                    )

                    # 6) Draw a distinct filled circle on the Index Finger Tip
                    cv2.circle(frame, tip_px, TIP_RADIUS + 3,
                               TIP_RING_CLR, -1)           # black shadow ring
                    cv2.circle(frame, tip_px, TIP_RADIUS,
                               TIP_FILL_CLR, -1)            # filled green dot

                    # 7) Overlay real-time coordinates and angle as text
                    #    Position the label to the upper-right of the fingertip
                    label_x = tip_px[0] + 18
                    label_y = tip_px[1] - 18

                    # Clamp labels so they don't go off-screen
                    label_x = min(label_x, w - 200)
                    label_y = max(label_y, 20)

                    draw_text(frame,
                              f"Tip: ({tip_px[0]}, {tip_px[1]})",
                              (label_x, label_y))

                    draw_text(frame,
                              f"Angle: {angle_deg:+.1f} deg",
                              (label_x, label_y + 26))

            # ── HUD footer ────────────────────────────────────────────────────
            draw_text(frame, "Press 'q' to quit",
                      (10, h - 12),
                      scale=0.48, thick=1,
                      color=(180, 180, 180))

            # ── Show frame ────────────────────────────────────────────────────
            cv2.imshow("Hand Tracker — Index Finger Direction", frame)

            # Exit cleanly when the user presses 'q'
            if cv2.waitKey(1) & 0xFF == ord('q'):
                break

    # ── Cleanup ───────────────────────────────────────────────────────────────
    cap.release()
    cv2.destroyAllWindows()
    print("[INFO] Camera released. Session ended.")


# ══════════════════════════════════════════════════════════════════════════════
#  ENTRY POINT
# ══════════════════════════════════════════════════════════════════════════════

if __name__ == "__main__":
    available_cameras = find_available_cameras(max_index=6)
    chosen_index      = select_camera(available_cameras)
    run(chosen_index)
